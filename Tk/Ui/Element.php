<?php
namespace Tk\Ui;

use Tk\Traits\SystemTrait;
use Tk\Ui\Traits\AttributesTrait;
use Tk\Ui\Traits\CssTrait;

/**
 * Use this object as a base class for renderable UI elements
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
abstract class Element
{
    use AttributesTrait;
    use CssTrait;
    use SystemTrait;

    /**
     * Return any html representing this element.
     * Override this method in extended objects
     */
    public function getHtml(): string
    {
        return '';
    }
}