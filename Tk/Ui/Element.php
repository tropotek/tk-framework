<?php
namespace Tk\Ui;

use Tk\Ui\Traits\AttributesTrait;

/**
 * Use this object as a base class to render UI elements
 */
abstract class Element
{
    use AttributesTrait;

    /**
     * Return any html string representing this element.
     * Override this method in extended objects
     */
    public function __toString(): string
    {
        return '';
    }
}