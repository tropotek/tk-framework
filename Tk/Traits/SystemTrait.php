<?php
namespace Tk\Traits;

use Tk\Config;
use Tk\Cookie;
use Tk\Factory;
use Tk\Registry;
use Tk\System;

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
        return $this->getSystem()->makePath($path);
    }

    public function makeUrl(string $path): string
    {
        return $this->getSystem()->makeUrl($path);
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

    public function getRequest(): \Symfony\Component\HttpFoundation\Request
    {
        return $this->getFactory()->getRequest();
    }

    public function getCookie(): Cookie
    {
        return $this->getFactory()->getCookie();
    }

}