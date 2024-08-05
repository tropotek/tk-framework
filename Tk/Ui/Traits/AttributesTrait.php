<?php
namespace Tk\Ui\Traits;

/**
 * This Trait can be used with object representing a DOM Element node
 * so that attributes and css classes can be managed.
 *
 * $attrList Source:
 *   array('style' => 'color: #000;', 'id' => '234', 'class' => 'text-center');
 *
 * Result calling getAttrString(true):
 *   <div class="text-center" id="234" style="color: #000;"></div>
 */
trait AttributesTrait
{

    protected array $_attrList = [];
    protected array $_cssList  = [];


    /**
     * NOTE: setting the 'class' attribute manually will overwrite
     * any class values set using the setCss() functions
     */
    public function setAttr(array|string $name, string $value = null): static
    {
        if (is_array($name)) {
            $this->_attrList = $this->_attrList + $name;
        } else {
            $name = strip_tags(trim($name));
            $this->_attrList[$name] = $value ?? $name;
        }
        return $this;
    }

    public function hasAttr(string $name): bool
    {
        return array_key_exists($name, $this->_attrList);
    }

    public function getAttr(string $name, string $default = ''): string
    {
        return $this->_attrList[$name] ?? $default;
    }

    public function removeAttr(string $name): static
    {
        if ($this->hasAttr($name)) {
            unset($this->_attrList[$name]);
        }
        return $this;
    }

    public function getAttrList(bool $withCss = false): array
    {
        if ($withCss && !($this->_attrList['class'] ?? false) && $this->getCssString()) {
            return $this->_attrList + ['class' => $this->getCssString()];
        }
        return $this->_attrList;
    }

    public function setAttrList(array $arr): static
    {
        $this->_attrList = $arr;
        return $this;
    }

    public function hasCss(string $css): bool
    {
        return array_key_exists(self::cleanCss($css), $this->_cssList);
    }

    public function addCss(string $css): static
    {
        foreach (explode(' ', $css) as $c) {
            if (!$c) continue;
            $c = self::cleanCss($c);
            $this->_cssList[$c] = $c;
        }
        return $this;
    }

    public function removeCss(string $css): static
    {
        foreach (explode(' ', $css) as $c) {
            if (!$c) continue;
            $c = self::cleanCss($c);
            unset($this->_cssList[$c]);
        }
        return $this;
    }

    public function getCssList(): array
    {
        return $this->_cssList;
    }

    public function setCssList(array $arr = []): static
    {
        $this->_cssList = $arr;
        return $this;
    }

    /**
     * return the css string in the form of a css class list
     * Eg:
     *   'class-one class-two class-three'
     */
    public function getCssString(): string
    {
        return trim(implode(' ', $this->_cssList));
    }

    /**
     * Return an attribute string that can be inserted into HTML
     *
     * Eg:
     *   'id="test-id" style="color: #000;" data-attr="test"'
     */
    public function getAttrString(bool $withCss = false): string
    {
        $str = '';
        if ($withCss && !($this->_attrList['class'] ?? false) && $this->getCssString()) {
            $str .= sprintf('class="%s" ', $this->getCssString());
        }
        foreach ($this->_attrList as $k => $v) {
            $str .= sprintf('%s="%s" ', $k, $v);
        }
        return trim($str);
    }

    /**
     * returns cleaned string for use as an HTML attribute value
     */
    public function escapeAttr(string $s): string
    {
        if (is_numeric($s)) return strval($s);
        if (empty($s)) return '';
        return htmlspecialchars($s, ENT_COMPAT | ENT_HTML401, "UTF-8", false);
    }

    /**
     * Clean CSS class name replacing non-alphanumeric chars with '-'
     */
    public static function cleanCss(string $css): string
    {
        return preg_replace("/\W|_/", "-", $css);
    }

}