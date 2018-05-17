<?php
namespace Tk\Dom;

/**
 * Class AttributesTrait
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2017 Michael Mifsud
 */
trait CssTrait
{

    /**
     * @var array
     */
    protected $cssList = array();


    /**
     * Clean  CSS class replacing '.' with '-'
     *
     * @param $class
     * @return mixed
     */
    public static function cleanCss($class)
    {
         if (is_array($class)) {
             $class = implode(' ', $class);
         }
        return str_replace('.', '-', $class);
    }

    /**
     * Does the css class exist
     *
     * @param string $class
     * @return bool
     */
    public function hasCss($class)
    {
        return array_key_exists($class, $this->cssList);
    }

    /**
     * Add a css class
     *
     * @param string $class
     * @param bool $fixName If set to true all '.' are replaced with '-' chars
     * @return $this
     */
    public function addCss($class, $fixName = true)
    {
        if ($fixName)
            $class = self::cleanCss($class);
        foreach (explode(' ', $class) as $c) {
            if (!$c) continue;
            $this->cssList[$c] = $c;
        }
        return $this;
    }

    /**
     * remove a css class
     *
     * @param string $class
     * @param bool $fixName If set to true all '.' are replaced with '-' chars
     * @return $this
     */
    public function removeCss($class, $fixName = true)
    {
        if ($fixName)
            $class = self::cleanCss($class);
        foreach (explode(' ', $class) as $c) {
            if (!$c) continue;
            unset($this->cssList[$c]);
        }
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