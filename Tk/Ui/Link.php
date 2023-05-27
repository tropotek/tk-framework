<?php
namespace Tk\Ui;

use Dom\Template;
use Tk\Uri;

/**
 * <code>
 *   \Tk\Ui\Link::create('Edit', \Tk\Uri::create('/dunno.html'))->addCss('btn-xs btn-success')->getHtml();
 * </code>
 */
class Link extends DomElement
{

    const ICON_LEFT = 'left';
    const ICON_RIGHT = 'right';

    protected string $text = '';

    protected string $iconPosition = self::ICON_LEFT;

    /**
     * The css value for the icon eg `fa fa-check`
     */
    protected string $icon = '';

    public function __construct(string $text = '', string|Uri $url = null)
    {
        $this->setText($text);
        $this->setAttr('title', $text);
        if ($url) $this->setUrl($url);
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;
        return $this;
    }

    public function getUrl(): ?Uri
    {
        return Uri::create($this->getAttr('href', ''));
    }

    public function setUrl(string|Uri $url): static
    {
        $this->setAttr('href', $url);
        return $this;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function setIcon(string $icon, string $iconPosition = null): static
    {
        $this->icon = $icon;
        if ($iconPosition) $this->setIconPosition($iconPosition);
        return $this;
    }

    public function getIconPosition(): string
    {
        return $this->iconPosition;
    }

    public function setIconPosition(string $iconPosition): static
    {
        $this->iconPosition = $iconPosition;
        return $this;
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();

        $template->setText('text', $this->getText());

        if ($this->getIcon()) {
            if ($this->getIconPosition() == self::ICON_LEFT) {
                $template->setVisible('icon-l');
                $template->addCss('icon-l', $this->getIcon());
            } else {
                $template->setVisible('icon-r');
                $template->addCss('icon-r', $this->getIcon());
            }
        } else {
            // this removed HTMX bug with tags in the button???
            $template->setText('element', $this->getText());
        }

        $template->addCss('element', $this->getCssList());
        $template->setAttr('element', $this->getAttrList());

        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<a var="element"><i choice="icon-l"></i><span var="text"></span><i choice="iconR"></i></a>
HTML;
        return $this->getFactory()->loadTemplate($html);
    }

}