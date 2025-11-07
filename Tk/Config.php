<?php
namespace Tk;

/**
 * This can hold all the systems configuration params.
 * Data in the config object is refreshed on each page load (not cached).
 */
final class Config extends Collection
{
    const string ENV_DEVELOPMENT = 'dev';
    const string ENV_PRODUCTION  = 'prod';

    private static mixed $_instance = null;


    protected function __construct()
    {
        parent::__construct();
    }

    protected function _init(): void
    {
        $this->set('base.path', System::discoverBasePath());
        $this->set('base.url', System::discoverBaseUrl());

        // default system paths
        $this->set('path.data', '/data');
        $this->set('path.cache', '/data/private/cache');
        $this->set('path.temp', '/data/private/tmp');
        $this->set('path.src', '/src');
        $this->set('path.config', '/src/config');
        $this->set('path.vendor', '/vendor');
        $this->set('path.vendor.org', '/vendor/ttek');
        $this->set('path.template', '/html');

        $this->set('php.date.timezone', 'Australia/Melbourne');

        $this->set('env.type', self::ENV_PRODUCTION);
        $this->set('log.logLevel', \Psr\Log\LogLevel::ERROR);
        $this->set('log.enableNoLog', true);

        $this->set('hostname', System::discoverHostname());

        ConfigLoader::create($this->get('base.path'))->loadConfigs($this);
    }

    /**
     * Gets an instance of this object, if none exists, one is created
     */
    public static function instance(): self
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
            self::$_instance->_init();
        }
        return self::$_instance;
    }

    /**
     * Static alias for self::instance()->get(...)
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        return self::instance()->get($key, $default);
    }

    /**
     * Static alias for self::instance()->set(...)
     */
    public static function setValue(string $key, mixed $value): self
    {
        return self::instance()->set($key, $value);
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


    public static function isProd(): bool
    {
        return self::instance()->get('env.type') == self::ENV_PRODUCTION;
    }

    public static function isDev(): bool
    {
        return self::instance()->get('env.type', self::ENV_DEVELOPMENT) == self::ENV_DEVELOPMENT;
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