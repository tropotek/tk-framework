<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Auth\Storage;

/**
 * 
 *
 * @package Tk\Auth\Storage
 */
interface Iface
{
    /**
     * Returns true if and only if storage is empty
     *
     * @return bool
     */
    public function isEmpty();

    /**
     * Returns the contents of storage
     * Behavior is undefined when storage is empty.
     *
     * @return mixed
     */
    public function read();

    /**
     * Writes $contents to storage
     *
     * @param  mixed $contents
     */
    public function write($contents);

    /**
     * Clears contents from storage
     *
     */
    public function clear();
}