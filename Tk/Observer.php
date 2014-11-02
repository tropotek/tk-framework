<?php
/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk;

/**
 * This object is the base for all observers
 * You could also use the splObserver objects and they should execute not a problem as they are the same interface.
 * 
 * @package Tk
 */
interface Observer
{
    
    
    /**
     * Update 
     * 
     * @param mixed $obs
     */
    public function update($obs);

}