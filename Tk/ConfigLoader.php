<?php
namespace Tk;

use Symfony\Component\Routing\Loader\Configurator\CollectionConfigurator;

/**
 * The configLoader class finds all config and routes file in the project and ttek libs.
 * Files can be loaded in a priority order by naming the file {priority}-config.php expected values
 * are 0-99
 *
 * Files in the main project config folder can omit the priority number as it defaults to 100.
 *
 * Files are executed from the lowest first to the highest last.
 * EG:
 *  o 10-config.php
 *  o 50-config.php
 *  o 100-config.php (same as the project root /src/config/config.php file)
 *
 * The route files are named with the same structure 50-routes.php and the site project is executed last.
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class ConfigLoader
{

    protected array $searchPaths = [];

    protected function __construct()
    {
        $vendorPath = dirname(dirname(__DIR__));
        $basePath = dirname(dirname($vendorPath));

        $libPaths = scandir($vendorPath);
        array_shift($libPaths);
        array_shift($libPaths);
        $this->searchPaths = [
            $basePath . '/src/config'
        ] + array_map(fn($path) => $vendorPath . '/' . $path . '/config' , $libPaths);
    }


    public static function create(): ConfigLoader
    {
        return new ConfigLoader();
    }

    /**
     * This method searches the site /src/config and all ttek lib folders
     * for config files named {priority}-config.php.
     *
     * The site config file can omit the priority value and just be named config.php as it will always
     * be executed last.
     *
     * The priority values can range from 0-99, 100 is reserved for the site config file that is executed last.
     * Lower values are executed first.
     *
     */
    public function loadConfigs(?Config $config = null): void
    {
        $this->load('/.+\/(([0-9]+)\-)?config\.php$/', $config);
    }

    /**
     * This method searches the site /src/config and all ttek lib folders
     * for route files named {priority}-routes.php.
     *
     * The site route file can omit the priority value and just be named config.php as it will always
     * be executed last. It will be treated as `100-config.php`
     *
     * The priority values can range from 0-99, 100 is reserved for the site route file that is executed last.
     * Lower values are executed first.
     *
     */
    public function loadRoutes(?CollectionConfigurator $routes = null): void
    {
        $this->load('/.+\/(([0-9]+)\-)?routes.php$/', $routes);
    }


    /**
     * Search the site and ttek lib for config files to load
     */
    public function load(string $regStr, mixed $object = null): void
    {
        // Find all tk config files $list[$priority][] = {path}
        $list = [];
        foreach ($this->searchPaths as $configPath) {
            if (!is_dir($configPath)) continue;
            $directory = new \RecursiveDirectoryIterator($configPath);
            $it = new \RecursiveIteratorIterator($directory);
            $regex = new \RegexIterator($it, $regStr, \RegexIterator::GET_MATCH);
            foreach($regex as $v) {
                $priority = $v[2] ?? '100';
                $list[$priority][] = $v[0];
            }
        }
        ksort($list);
        foreach ($list as $priority => $files) {
            foreach ($files as $path) {
                $result = include $path;
                if (is_callable($result) && $object) {
                    $result($object);

                }
            }
        }
    }

}