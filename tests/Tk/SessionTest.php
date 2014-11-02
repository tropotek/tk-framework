<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Test;

use \Tk\Config as Config;

/**
 *
 * @package Tk\Test
 */
class SessionTest extends \PHPUnit_Framework_TestCase
{




    public function __construct()
    {
        $session = Config::getInstance()->getSession();
        parent::__construct('Session Test');
    }

    public function setUp()
    {

    }

    public function tearDown()
    {

    }



    public function testCreate()
    {
        $session = Config::getInstance()->getSession();
        $session->set('test.var1', 'testtest');
        $t = $session->exists('test.var1');
        $this->assertTrue($t);


    }

    public function testObject()
    {
        $session = Config::getInstance()->getSession();
        $session->set('test.var1', 'testtest');
        $this->assertEquals($session->getOnce('test.var1'), 'testtest');
        $this->assertFalse($session->exists('test.var1'));
        $this->assertEquals($session->getName(), Config::getInstance()->get('session.name'));


    }


}

