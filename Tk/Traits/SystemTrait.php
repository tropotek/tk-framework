<?php
namespace Tk\Traits;

use Tk\Config;
use Tk\Factory;
use Tk\Registry;
use Tk\System;

/**
 *
 * @author Tropotek <http://www.tropotek.com/>
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
}