<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Str;

/**
 * 
 * 
 * @package Tk\Str
 */
abstract class Iface  {

    const TAB = '  ';

    /**
     * Return a string representation of this object
     *
     * @return string
     */
    abstract public function toString();


    /**
     * PHP magic method...
     *
     * @return type
     */
    final public function __toString()
    {
        return $this->toString();
    }
    
}