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

    public function getHostname(): string
    {
        return $this->get('hostname', '');
    }

    public function getBasePath(): string
    {
        return $this->get('base.path', '');
    }

    public function getBaseUrl(): string
    {
        return $this->get('base.url', '');
    }

    public function getDataPath(): string
    {
        return System::makePath($this->get('path.data'));
    }

    public function getDataUrl(): string
    {
        return System::makeUrl($this->get('path.data'));
    }

    public function getTempPath(): string
    {
        return System::makePath($this->get('path.temp'));
    }

    public function getTempUrl(): string
    {
        return System::makeUrl($this->get('path.temp'));
    }

    public function getCachePath(): string
    {
        return System::makePath($this->get('path.cache'));
    }

    public function getCacheUrl(): string
    {
        return System::makeUrl($this->get('path.cache'));
    }

    public function getTemplatePath(): string
    {
        return System::makePath($this->get('path.template'));
    }

    public function getTemplateUrl(): string
    {
        return System::makeUrl($this->get('path.template'));
    }

    public function isDebug(): bool
    {
        return $this->get('debug', false);
    }

    public function isProd(): bool
    {
        return $this->get('env.type', 'dev') == 'prod';
    }

    public function isDev(): bool
    {
        return $this->get('env.type', 'dev') == 'dev';
    }

    /**
     * Return a group of entries from the config with similar keys
     *
     * For example if the prefixName = 'app.site'
     * It will return all registry values with the key starting with `app.site.____`
     *
     * Set $truncateKeys to true to remove the $prefixName portion from the found keys.
     */
    public function getGroup(string $prefixName, bool $truncateKeys = false): array
    {
        $prefixName = rtrim($prefixName, '.');
        $regex = '/^' . $prefixName . '\./';
        $found = Collection::findByRegex($this->all(), $regex);
        if ($truncateKeys) {
            foreach ($found as $k => $v) {
                $found[str_replace($prefixName.'.', '', $k)] = $v;
                unset($found[$k]);
            }
        }
        return $found;
    }
}