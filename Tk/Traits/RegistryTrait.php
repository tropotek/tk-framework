<?php
namespace Tk\Traits;

use Tk\Registry;

trait RegistryTrait
{

    public function getRegistry(): ?Registry
    {
        $r = null;
        try {
            $r = Registry::instance();
        } catch(\Exception $e) {}
        return $r;
    }

}