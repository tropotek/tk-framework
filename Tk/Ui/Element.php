<?php
namespace Tk\Ui;

use Tk\Traits\SystemTrait;
use Tk\Ui\Traits\AttributesTrait;
use Tk\Ui\Traits\CssTrait;

/**
 * Use this object as a base class to render UI elements
 */
abstract class Element
{
    use AttributesTrait;
    use CssTrait;
    use SystemTrait;

    /**
     * Return any html string representing this element.
     * Override this method in extended objects
     */
    public function __toString(): string
    {
        return '';
    }
}