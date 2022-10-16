<?php
namespace Tk\Traits;

use Tk\Factory;

/**
 *
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
trait FactoryTrait
{
    public function getFactory(): Factory
    {
        return Factory::instance();
    }
}