<?php
namespace Tk\Ui;


use Tk\Uri;

/**
 * <code>
 *   \Tk\Ui\Link::create('Edit', \Tk\Uri::create('/dunno.html'))->addCss('btn-xs btn-success')->getHtml();
 * </code>
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class Link extends Element
{

    protected string $text = '';

    public function __construct(string $text = '')
    {
        $this->setText($text);
        $this->setAttr('title', $text);
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

    public function getUrl(): string|Uri
    {
        return $this->getAttr('href', '');
    }

    public function setUrl(string|Uri $url): static
    {
        $this->setAttr('href', $url);
        return $this;
    }

    public function getHtml(): string
    {
        $html = '';
        $html .= sprintf('<a %s class="%s">%s</a>',
            $this->getAttrString(),
            $this->getCssString(),
            htmlspecialchars($this->getText())
        );
        return $html;
    }

}