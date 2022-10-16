<?php
namespace Tk\Traits;

use Tk\Config;

/**
 *
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
trait ConfigTrait
{

    public function getConfig(): Config
    {
        return Config::instance();
    }
}