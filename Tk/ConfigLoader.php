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
 *  o 10-config.php (run first)
 *  o 50-config.php
 *  o 100-config.php (ran last, same as the site root /config.php file)
 *
 * The route files are named with the same structure 50-routes.php and the site project is executed last.
 *
 * @todo implement a caching strategy for this ???
 *       Be sure to store it somewhere that a user cannot read,
 *       a serialized string in a php file in the cache folder could be an option
 */
class ConfigLoader
{

    protected array $searchPaths = [];

    protected function __construct()
    {
        $vendorPath = dirname(__DIR__, 2);
        $basePath = dirname($vendorPath, 2);

        // Get all searchable paths
        $libPaths = scandir($vendorPath);
        array_shift($libPaths);
        array_shift($libPaths);

        $this->searchPaths = array_map(fn($path) => $vendorPath . '/' . $path . '/config' , $libPaths);
        array_unshift($this->searchPaths, $basePath . '/src/config');
    }

    public static function create(): ConfigLoader
    {
        return new ConfigLoader();
    }

    /**
     * Load all config files in order of priority
     *
     * The site config file can omit the priority value and just be named config.php as it will always
     * be executed last.
     *
     */
    public function loadConfigs(?Config $config = null): void
    {
        $list = $this->findFiles('config.php');
        foreach ($list as $path) {
            $this->load($path, $config);
        }
        // load site config
        $this->load($config->getBasePath() . '/config.php', $config);
    }

    /**
     * Load all route files in order of priority
     *
     * The site rout file can omit the priority value and just be named rout.php as it will always
     * be executed last.
     *
     */
    public function loadRoutes(?CollectionConfigurator $routes = null): void
    {
        $list = $this->findFiles('routes.php');
        foreach ($list as $path) {
            $this->load($path, $routes);
        }
    }

    /**
     * Find files that match the file basename and return them in priority from lowest to highest
     *
     * This method searches the site /src/config and all ttek lib config folders
     * for route files named `{priority}-{basename.php}`
     *
     * The site route file can omit the priority value and just be named config.php as it will always
     * be executed last. It will be treated as `100-{basename.php}`
     *
     * The priority values can range from 0-99, 100 is reserved for the site route file that is executed last.
     * Lower values are executed first.
     *
     * @param string $basename
     * @return array
     */
    public function findFiles(string $basename): array
    {
        // Find all tk config files $list[$priority][] = {path}
        $list = [];
        foreach ($this->searchPaths as $configPath) {
            if (!is_dir($configPath)) continue;
            $directory = new \RecursiveDirectoryIterator($configPath);
            $it = new \RecursiveIteratorIterator($directory);
            $reg = sprintf('/.+\/(([0-9]+)\-)?%s$/', preg_quote($basename));
            $regex = new \RegexIterator($it, $reg, \RegexIterator::GET_MATCH);
            foreach($regex as $v) {
                $priority = $v[2] ?? '100';
                if (!isset($list[$priority]) || !in_array($v[0], $list[$priority])) {
                    $list[$priority][] = $v[0];
                }
            }
        }
        ksort($list);

        // Flatten the array
        $result = [];
        array_walk_recursive($list,function($v) use (&$result){ $result[] = $v; });
        return $result;
    }

    /**
     * Search the site and ttek lib for config files to load
     */
    public function load(string $path, mixed $object = null): void
    {
        if (!is_file($path)) return;
        $callback = include $path;
        if (is_callable($callback) && $object) {
            $callback($object);
        }
    }

}