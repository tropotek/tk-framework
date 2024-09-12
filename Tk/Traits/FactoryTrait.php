<?php
namespace Tk\Traits;

use Bs\Factory;

/**
 * @deprecated Use Factory::instance()
 */
trait FactoryTrait
{
    public function getFactory(): Factory
    {
        return Factory::instance();
    }
}