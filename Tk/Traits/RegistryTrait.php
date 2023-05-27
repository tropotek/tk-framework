<?php
namespace Tk\Traits;

use Tk\Registry;

trait RegistryTrait
{

    public function getRegistry(): Registry
    {
        return Registry::instance();
    }

}