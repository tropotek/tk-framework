<?php
namespace Tk;

/**
 * The configLoader class finds all {basename} files in the search paths.
 * Files can be loaded in a priority order by naming the file {priority}-{basename} expected values
 * are 0-99
 *
 * Files are executed from the lowest first to the highest last.
 * EG:
 *  o 10-config.php (run first)
 *  o 50-config.php
 *  o 100-config.php (ran last, before /src/config/config.php, /config.php files)
 *
 * Config files are expected to be '.php' file in the following format, using `\Tk\Config` as an example:
 * ```php
 *  <?php
 *  use Tk\Config;
 *  return function (Config $config) {
 *    // setup the config object as needed
 *  };
 *  ?>
 * ```
 *
 *
 */
class ConfigLoader
{

    protected string $basePath    = '';
    protected array  $searchPaths = [];
    protected int    $ttl         = 0;


    protected function __construct(int $cacheTtl = 0)
    {
        $this->ttl = $cacheTtl;
        $vendorPath = dirname(__DIR__, 2);
        $this->basePath = Config::instance()->get('base.path', dirname($vendorPath, 2));

        // Get all searchable paths
        $libPaths = scandir($vendorPath);
        array_shift($libPaths);
        array_shift($libPaths);
        $this->searchPaths = array_map(fn($path) => $vendorPath . '/' . $path . '/config' , $libPaths);
    }

    public static function create(int $cacheTtl = 0): ConfigLoader
    {
        return new ConfigLoader($cacheTtl);
    }

    /**
     * Load all config files in order of priority
     *
     * The site config file can omit the priority value and just be named config.php as it will always
     * be executed last.
     */
    public function loadConfigs(mixed $object = null, string $basename = 'config.php'): void
    {
        $list = $this->findFiles($basename);
        foreach ($list as $path) {
            $this->load($path, $object);
        }
        // load configs
        $this->load($this->basePath . '/src/config/'.$basename, $object);
        $this->load($this->basePath . '/'.$basename, $object);
    }


    /**
     * Find files that match the file basename and return them in priority from lowest to highest
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
     * execute the config callback within the file
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