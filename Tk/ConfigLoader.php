<?php
namespace Tk;


use Symfony\Component\Routing\RouteCollection;

/**
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
    public function loadRoutes(?RouteCollection $routeCollection = null): void
    {
        $this->load('/.+\/(([0-9]+)\-)?routes.php$/', $routeCollection);
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