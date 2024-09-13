<?php
namespace Tk\Ui;

use Dom\Template;
use Tk\Uri;

/**
 * <code>
 *   \Tk\Ui\Button::create('Edit', \Tk\Uri::create('/dunno.html'), 'fa fa-edit)->addCss('btn-xs btn-success')->show();
 * </code>
 */
class Button extends Link
{

    protected ?Uri $url = null;


    public function getUrl(): ?Uri
    {
        return $this->url;
    }

    public function setUrl(string|Uri $url): static
    {
        $this->url = Uri::create($url);
        return $this;
    }

    public function show(): ?Template
    {
        if ($this->getUrl()) {
            $this->setAttr('onclick', 'location.href =\''.$this->getUrl().'\'');
        }
        return parent::show();
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<button type="button" var="element"><i choice="icon-l"></i> <span var="text"></span> <i choice="iconR"></i></button>
HTML;
        return Template::load($html);
    }
}
