<?php
namespace Tk\Dom;

/**
 * Class AttributesTrait
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2017 Michael Mifsud
 */
trait CssTrait
{

    /**
     * @var array
     */
    protected $cssList = array();



    /**
     * Add a css class
     *
     * @param string $class
     * @return $this
     */
    public function addCss($class)
    {
        $this->cssList[$class] = $class;
        return $this;
    }

    /**
     * remove a css class
     *
     * @param string $class
     * @return $this
     */
    public function removeCss($class)
    {
        unset($this->cssList[$class]);
        return $this;
    }

    /**
     * Get the css class list
     *
     * @return array
     */
    public function getCssList()
    {
        return $this->cssList;
    }

    /**
     * Set the css cell class list
     * If no parameter sent the array is cleared.
     *
     * @param array $arr
     * @return $this
     */
    public function setCssList($arr = array())
    {
        $this->cssList = $arr;
        return $this;
    }

    /**
     * return the css string in the form of a css class list
     * Eg:
     *   'class-one class-two class-three'
     *
     * @return string
     */
    public function getCssString()
    {
        return trim(implode(' ', $this->cssList));
    }

}