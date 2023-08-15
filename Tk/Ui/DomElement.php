<?php
namespace Tk\Ui;

use Dom\Renderer\RendererInterface;
use Dom\Renderer\Traits\RendererTrait;

/**
 * Use this object as a base class for Template rendered UI elements
 */
abstract class DomElement extends Element implements RendererInterface
{
    use RendererTrait;

    public function __toString(): string
    {
        return $this->show()->toString();
    }

}