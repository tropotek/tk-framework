<?php
namespace Tk\Dom;

/**
 * Class AttributesTrait
 *
 * This Trait can be used with object representing a DOM Element node
 * so that attributes can be managed
 *
 * $attrList Source:
 *   array('style' => 'color: #000;', 'id' => 'test-id');
 *
 * Rendered Result:
 *   <div id="test-id" style="color: #000;"></div>
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2017 Michael Mifsud
 */
trait AttributesTrait
{

    /**
     * @var array
     */
    protected $attrList = array();


    /**
     * Set an attribute
     *
     * @param string|array $name
     * @param string $value
     * @return $this
     */
    public function setAttr($name, $value = null)
    {
        if (is_array($name)) {
            $this->attrList = array_merge($this->attrList, $name);
        } else {
            $name = strip_tags(trim($name));
            if ($value === null) {
                $value = $name;
            }
            $this->attrList[$name] = $value;
        }
        return $this;
    }

    /**
     * Does the attribute exist
     *
     * @param string $name
     * @return bool
     */
    public function hasAttr($name)
    {
        return array_key_exists($name, $this->attrList);
    }

    /**
     * Get an attributes value string
     *
     * @param $name
     * @return string
     */
    public function getAttr($name)
    {
        if (isset($this->attrList[$name])) {
            return $this->attrList[$name];
        }
        return '';
    }

    /**
     * remove an attribute
     *
     * @param string $name
     * @return $this
     */
    public function removeAttr($name)
    {
        unset($this->attrList[$name]);
        return $this;
    }

    /**
     * Get the attributes list
     *
     * @return array
     */
    public function getAttrList()
    {
        return $this->attrList;
    }

    /**
     * Set the attributes list
     * If no parameter sent the array is cleared.
     *
     * @param array $arr
     * @return $this
     */
    public function setAttrList($arr = array())
    {
        $this->attrList = $arr;
        return $this;
    }

    /**
     * Return an attribute string that can be inserted into HTML
     *
     * Eg:
     *   'id="test-id" style="color: #000;" data-attr="test"'
     *
     * @return string
     */
    public function getAttrString()
    {
        $str = '';
        foreach ($this->attrList as $k => $v) {
            $str = sprintf('%s="%s" ', $k, $v);
        }
        return trim($str);
    }

}