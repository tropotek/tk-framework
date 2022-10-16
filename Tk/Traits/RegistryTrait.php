<?php
namespace Tk\Traits;

use Tk\Registry;

/**
 * @author Tropotek <http://www.tropotek.com/>
 */
trait RegistryTrait
{

    public function getRegistry(): Registry
    {
        return Registry::instance();
    }
}