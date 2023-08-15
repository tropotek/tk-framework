<?php
namespace Tk\Traits;

use Tk\Factory;

trait FactoryTrait
{
    public function getFactory(): Factory
    {
        return Factory::instance();
    }
}