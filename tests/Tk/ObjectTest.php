<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Test;

use \Tk\Object as Object;
/**
 *
 * @package Tk\Test
 */
class ObjectTest extends \PHPUnit_Framework_TestCase
{

    public function __construct()
    {
        parent::__construct('Sys Object Test');
    }

    public function setUp()
    {

    }

    public function tearDown()
    {

    }



    public function testId()
    {
        $obj1 = new Object1();
        $obj2 = new Object1();

        $this->assertNotEqual($obj1->getInstanceId(), $obj2->getInstanceId(), 'Obj1: ' . $obj1->getInstanceId() . ', Obj2: ' . $obj2->getInstanceId());

    }


    public function testObjectKey()
    {
        $obj1 = new Object1();
        $this->assertTrue(preg_match('/event_[0-9]+/', $obj1->getObjectKey('event')) == true);
    }

    public function testClassConstants()
    {
        $obj1 = new Object1();
        $arr = $obj1->getConstantList();
        $this->assertTrue(is_array($arr));
    }

    public function testToString()
    {
        $obj1 = new Object1();
        $str = $obj1->toString();
        $this->assertTrue(preg_match('|^Tk\\\\Test\\\\Object1|', $str) == true);

    }


}

class Object1 extends Object {

}
