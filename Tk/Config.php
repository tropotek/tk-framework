<?php
namespace Tk;

use Psr\Log\LogLevel;
use Tk\Traits\SingletonTrait;
use Tk\Traits\SystemTrait;

/**
 * This will hold all the systems configuration params.
 * All data in the config object is recreated each session.
 *
 * Query this when looking for a system configuration value.
 *
 * NOTE: No objects should be saved in the Config storage, only primitive types.
 *
 *
 * @author Tropotek <http://www.tropotek.com/>
 *
 */
class Config extends Collection
{
    use SingletonTrait;
    Use SystemTrait;


    protected function __construct()
    {
        $this->set('script.start.time', microtime(true));
        parent::__construct();

        $this->set('base.path', $this->getSystem()->discoverBasePath());
        $this->set('base.url', $this->getSystem()->discoverBaseUrl());

        ConfigLoader::create()->loadConfigs($this);
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