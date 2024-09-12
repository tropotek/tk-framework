<?php
namespace Tk\Traits;

use Bs\Registry;

/**
 * @deprecated use Registry::instance()
 */
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