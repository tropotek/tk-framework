<?php
namespace Tk\Traits;

use Tk\Config;
use Tk\Cookie;
use Bs\Factory;
use Bs\Registry;
use Tk\System;

/**
 *
 * @deprecated System is now all static methods
 */
trait SystemTrait
{
    /**
     * @return System|\App\System
     */
    public function getSystem(): System
    {
        return System::instance();
    }

    /**
     * @return Factory|\App\Factory
     */
    public function getFactory(): Factory
    {
        return $this->getSystem()->getFactory();
    }

    /**
     * @return Config|\App\Config
     */
    public function getConfig(): Config
    {
        return $this->getSystem()->getConfig();
    }

    /**
     * @return Registry|\App\Registry
     */
    public function getRegistry(): Registry
    {
        return $this->getSystem()->getRegistry();
    }

    public function makePath(string $path): string
    {
        return System::makePath($path);
    }

    public function makeUrl(string $path): string
    {
        return System::makeUrl($path);
    }

    // Helper Functions

    public function loadTemplate(string $xhtml = ''): ?\Dom\Template
    {
        return $this->getFactory()->getTemplateLoader()->load($xhtml);
    }

    public function loadTemplateFile(string $path = ''): ?\Dom\Template
    {
        return $this->getFactory()->getTemplateLoader()->loadFile($path);
    }

    public function getCookie(): Cookie
    {
        return $this->getFactory()->getCookie();
    }

}