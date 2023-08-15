<?php
namespace Tk\Traits;

use Tk\Config;

trait ConfigTrait
{

    public function getConfig(): Config
    {
        return Config::instance();
    }
}