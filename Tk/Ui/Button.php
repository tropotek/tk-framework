<?php
namespace Tk\Ui;


use Dom\Template;

/**
 * <code>
 *   \Tk\Ui\Button::create('Edit', \Tk\Uri::create('/dunno.html'), 'fa fa-edit)->addCss('btn-xs btn-success')->show();
 * </code>
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class Button extends DomElement
{

    const ICON_LEFT = 'left';
    const ICON_RIGHT = 'right';

    protected string $text = '';

    /**
     * The css value for the icon eg `fa fa-check`
     */
    protected string $icon = '';

    protected string $iconPosition = self::ICON_LEFT;


    public function __construct(string $text)
    {
        $this->setText($text);
        $this->setAttr('title', $text);
        $this->addCss('btn btn-secondary');
    }

    public static function createButton(string $text): static
    {
        $obj = new static($text);
        return $obj;
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

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;
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
<button type="button" var="element"><i choice="icon-l"></i><span var="text"></span><i choice="iconR"></i></button>
HTML;
        return $this->getFactory()->loadTemplate($html);
    }
}
