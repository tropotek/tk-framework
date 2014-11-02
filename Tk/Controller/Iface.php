<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Controller;

/**
 * Controllers are used to populate the front controller and give it
 * functionality. With the right command objects in the right order
 * you create your web applications.
 *
 * Controllers are executed in order of addition
 *
 * @see \Tk\FrontController
 * @package Tk\Controller
 */
interface Iface extends \Tk\Observer { }
