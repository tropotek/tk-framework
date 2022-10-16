<?php
namespace Tk;

use Psr\Log\LogLevel;
use Tk\Traits\SingletonTrait;
use Tk\Traits\SystemTrait;

/**
 * This will hold all the systems configuration params.
 * Query this when looking for a system configuration value from anywhere in the code.
 *
 * NOTE: No objects should be saved in the Config storage, only primitive types.
 *
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class Config extends Collection
{
    use SingletonTrait;
    Use SystemTrait;


    protected function __construct()
    {
        parent::__construct();

        $this->set('script.start.time', microtime(true));

        // Site paths
        $this->set('base.path', $this->getSystem()->discoverBasePath());
        $this->set('base.url', $this->getSystem()->discoverBaseUrl());

        $this->set('path.data',         '/data');
        $this->set('path.cache',        '/data/cache');
        $this->set('path.temp',         '/data/temp');
        $this->set('path.src',          '/src');
        $this->set('path.config',       '/src/config');
        $this->set('path.vendor',       '/vendor');
        $this->set('path.vendor.org',   '/vendor/ttek');
        $this->set('path.template',     '/html');

        $this->set('path.routes',       '/src/config/routes.php');
        $this->set('path.routes.cache', '/data/cache/routes.cache.php');

        // Session Defaults
        $this->set('session.db_enable',         false);
        $this->set('session.db_table',          '_session');
        $this->set('session.db_id_col',         'session_id');
        $this->set('session.db_data_col',       'data');
        $this->set('session.db_lifetime_col',   'lifetime');
        $this->set('session.db_time_col',       'time');

        $this->set('debug', false);
        $this->set('log.system.request', $this->get('path.temp') . '/requestLog.txt');
        $this->set('log.logLevel', LogLevel::ERROR);

        // Set the timezone in the config.ini
        //$this->set('php.date.timezone', 'Australia/Melbourne');

    }

    public function getBasePath(): string
    {
        return $this->get('base.path');
    }

    public function getBaseUrl(): string
    {
        return $this->get('base.url');
    }

    public function getDataPath(): string
    {
        return $this->getSystem()->makePath($this->get('path.data'));
    }

    public function getDataUrl(): string
    {
        return $this->getSystem()->makeUrl($this->get('path.data'));
    }

    public function getTempPath(): string
    {
        return $this->getSystem()->makePath($this->get('path.temp'));
    }

    public function getTempUrl(): string
    {
        return $this->getSystem()->makeUrl($this->get('path.temp'));
    }

    public function getCachePath(): string
    {
        return $this->getSystem()->makePath($this->get('path.cache'));
    }

    public function getCacheUrl(): string
    {
        return $this->getSystem()->makeUrl($this->get('path.cache'));
    }

    public function getTemplatePath(): string
    {
        return $this->getSystem()->makePath($this->get('path.template'));
    }

    public function getTemplateUrl(): string
    {
        return $this->getSystem()->makeUrl($this->get('path.template'));
    }


    public function isDebug(): bool
    {
        return $this->get('debug', false);
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