<?php
namespace Tk\Traits;

use Tk\Config;

/**
 * @deprecated use Config::instance()
 */
trait ConfigTrait
{

    public function getConfig(): Config
    {
        return Config::instance();
    }
}