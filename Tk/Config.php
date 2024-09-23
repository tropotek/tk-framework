<?php
namespace Tk;

/**
 * This can hold all the systems configuration params.
 * All data in the config object is recreated each session.
 * NOTE: No objects should be saved in the Config storage, only primitive types.
 */
class Config extends Collection
{
    protected static mixed $_instance = null;

    protected function __construct()
    {
        $this->set('script.start.time', microtime(true));
        parent::__construct();

        $this->set('hostname', $_SERVER['HTTP_HOST'] ?? $_SERVER['HTTP_X_FORWARDED_HOST'] ?? 'localhost');
        $this->set('base.path', System::discoverBasePath() ?? '');
        $this->set('base.url', system::discoverBaseUrl() ?? '');

        // default system paths
        $this->set('path.data',       '/data');
        $this->set('path.cache',      '/data/cache');
        $this->set('path.temp',       '/data/tmp');
        $this->set('path.src',        '/src');
        $this->set('path.config',     '/src/config');
        $this->set('path.vendor',     '/vendor');
        $this->set('path.vendor.org', '/vendor/ttek');
        $this->set('path.template',   '/html');

        $this->set('php.date.timezone', 'Australia/Melbourne');

        $this->set('debug',           false);
        $this->set('env.type',        'prod');
        $this->set('log.logLevel',    \Psr\Log\LogLevel::ERROR);
        $this->set('log.enableNoLog', true);
    }

    /**
     * Gets an instance of this object, if none exists one is created
     */
    public static function instance(): static
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new static();
            ConfigLoader::create()->loadConfigs(self::$_instance);
        }
        return self::$_instance;
    }

    public static function getHostname(): string
    {
        return self::instance()->get('hostname', '');
    }

    public static function getBasePath(): string
    {
        return self::instance()->get('base.path', '');
    }

    public static function getBaseUrl(): string
    {
        return self::instance()->get('base.url', '');
    }

    public static function getDataPath(): string
    {
        return self::instance()->get('path.data', '');
    }

    public static function getTempPath(): string
    {
        return self::instance()->get('path.temp', '');
    }

    public static function getCachePath(): string
    {
        return self::instance()->get('path.cache', '');
    }

    public static function getTemplatePath(): string
    {
        return self::instance()->get('path.template', '');
    }

    public static function isDebug(): bool
    {
        return self::instance()->get('debug', false);
    }

    public static function isProd(): bool
    {
        return self::instance()->get('env.type', 'dev') == 'prod';
    }

    public static function isDev(): bool
    {
        return self::instance()->get('env.type', 'dev') == 'dev';
    }


    /**
     * Create a full filepath to a resource using the relative path
     * This method will strip the trailing slash.
     * If no DIRECTORY_SEPARATOR is at the beginning of the $path one will be prepended
     */
    public static function makePath(string $path): string
    {
        $path = FileUtil::getRealPath($path);
        $path = rtrim($path, DIRECTORY_SEPARATOR);
        $path = str_replace(self::getBasePath(), '', $path); // Prevent recurring
        return self::getBasePath() . $path;
    }

    /**
     * Create a full path URL from a relative path
     * This method will strip the trailing slash.
     * If a full URL is supplied only the path is returned
     */
    public static function makeUrl(string $path): string
    {
        $path = FileUtil::getRealPath($path);
        $path = rtrim($path, DIRECTORY_SEPARATOR);
        $path = parse_url($path, \PHP_URL_PATH);
        $path = str_replace(self::getbaseUrl(), '', $path); // Prevent recurring
        return self::getbaseUrl() . $path;
    }

    /**
     * Return a group of entries from the config with similar keys
     *
     * For example if the prefixName = 'app.site'
     * It will return all registry values with the key starting with `app.site.____`
     *
     * Set $truncateKeys to true to remove the $prefixName portion from the found keys.
     */
    public static function getGroup(string $prefixName, bool $truncateKeys = false): array
    {
        $prefixName = rtrim($prefixName, '.');
        $regex = '/^' . $prefixName . '\./';
        $found = Collection::findByRegex(self::instance()->all(), $regex);
        if ($truncateKeys) {
            foreach ($found as $k => $v) {
                $found[str_replace($prefixName.'.', '', $k)] = $v;
                unset($found[$k]);
            }
        }
        return $found;
    }
}