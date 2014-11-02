<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Command;

/**
 * A Command object is used by objects that can be
 * executed by themselves calling Tk\Object::execute()
 *
 * @package Tk\Command
 */
interface Iface
{
    /**
     * Execute
     *
     */
    public function execute();


}
