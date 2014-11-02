<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Test;

use \Tk\Model\DataMap as DataMap;
use \Tk\Model\Model as Model;
use \Tk\Model\Map as Map;
/**
 *
 * @package Tk\Test
 */
class DataMapTest extends \PHPUnit_Framework_TestCase
{

    public function __construct()
    {
        parent::__construct('Db Map Test');
        \Tk\Config::getInstance();
    }

    public function setUp()
    {

    }

    public function tearDown()
    {

    }


    /**
     *
     */
    public function testArray()
    {
//        $map = new Tk_Db_Array();
//        $this->assertEquals($map->getPropertyName(), 'dataArray');
//        $arr = $map->getColumnNames();
//        $this->assertEquals(current($arr), 'dataArray');

//        $obj = new \stdClass();
//        $obj->dataArray = array('Item 1' => 'Value 1', 'Item 2' => 32,
//            'Item 3' => 3.23, 'Item 4' => true);
//        $cval = $map->getColumnValue($obj);
//
//        $str = 'YTo0OntzOjY6Ikl0ZW0gMSI7czo3OiJWYWx1ZSAxIjtzOjY6Ikl0ZW0gMiI7aTozMjtzOjY6Ikl0ZW0gMyI7ZDozLjIyOTk5OTk5OTk5OTk5OTk4MjIzNjQzMTYwNTk5NzQ5NTM1MzIyMTg5MzMxMDU0Njg3NTtzOjY6Ikl0ZW0gNCI7YjoxO30=';
//        $this->assertEquals($cval['dataArray'], enquote($str), 'Base64 Encoded string Expected');
//
//        $row = array('dataArray' => $str, 'ItemMisc' => 'test');
//        $pval = $map->getPropertyValue($row);
//
//        $this->assertEquals($pval['Item 1'], 'Value 1');
//        $this->assertEquals($pval['Item 2'], 32);
//        $this->assertEquals($pval['Item 3'], 3.23);
//        $this->assertEquals($pval['Item 4'], true);
//
    }


    /**
     *
     */
    public function testString()
    {
        $map = Map\String::create('data');
        $this->assertEquals($map->getPropertyName(), 'data');
        $arr = $map->getColumnNames();
        $this->assertEquals(current($arr), 'data');

        $str = 'This is a test <em>String</em>.';

        $obj = new \stdClass();
        $obj->data = $str;
        $cval = $map->getColumnValue($obj);
        $this->assertEquals($cval['data'], enquote($str), 'String Expected');

        $row = array('data' => $str, 'ItemMisc' => 'test');
        $pval = $map->getPropertyValue($row);

        $this->assertEquals($pval, $str);

    }


    /**
     *
     */
    public function testStringEncrypt()
    {
        $map = Map\StringEncrypt::create('data');
        $this->assertEquals($map->getPropertyName(), 'data');
        $arr = $map->getColumnNames();
        $this->assertEquals(current($arr), 'data');

        $str = 'This is a test <em>String</em>.';
        $enc = 'lKip0mTO2YHWjOjEx79/fKWtfrK418/P3KijxMGJjQ==';

        $obj = new \stdClass();
        $obj->data = $str;
        $cval = $map->getColumnValue($obj);

        $this->assertEquals($cval['data'], enquote($enc), 'String Expected');

        $row = array('data' => $enc, 'ItemMisc' => 'test');
        $pval = $map->getPropertyValue($row);
        $this->assertEquals($pval, $str);

    }


    /**
     *
     */
    public function testInteger()
    {
        $map = Map\Integer::create('data');
        $this->assertEquals($map->getPropertyName(), 'data');
        $arr = $map->getColumnNames();
        $this->assertEquals(current($arr), 'data');

        $val = 564653;

        $obj = new \stdClass();
        $obj->data = $val;
        $cval = $map->getColumnValue($obj);
        $this->assertEquals($cval['data'], $val);

        $row = array('data' => $val, 'ItemMisc' => 'test');
        $pval = $map->getPropertyValue($row);

        $this->assertEquals($pval, $val);

    }

    /**
     *
     */
    public function testFloat()
    {
        $map = Map\Float::create('data');
        $this->assertEquals($map->getPropertyName(), 'data');
        $arr = $map->getColumnNames();
        $this->assertEquals(current($arr), 'data');

        $val = 5643.8654;

        $obj = new \stdClass();
        $obj->data = $val;
        $cval = $map->getColumnValue($obj);
        $this->assertEquals($cval['data'], $val);

        $row = array('data' => $val, 'ItemMisc' => 'test');
        $pval = $map->getPropertyValue($row);

        $this->assertEquals($pval, $val);

    }

    /**
     *
     */
    public function testBoolean()
    {
        $map = Map\Boolean::create('data');
        $this->assertEquals($map->getPropertyName(), 'data');
        $arr = $map->getColumnNames();
        $this->assertEquals(current($arr), 'data');

        $val = true;

        $obj = new \stdClass();
        $obj->data = $val;
        $cval = $map->getColumnValue($obj);
        $this->assertEquals($cval['data'], 1);

        $row = array('data' => $val, 'ItemMisc' => 'test');
        $pval = $map->getPropertyValue($row);

        $this->assertEquals($pval, $val);

    }

    /**
     *
     */
    public function testDataUrl()
    {
//        $map = Map\DataUrl::create('data');
//        $this->assertEquals($map->getPropertyName(), 'data');
//        $arr = $map->getColumnNames();
//        $this->assertEquals(current($arr), 'data');
//
//        $val = '/path/to/File.xml';
//
//        $obj = new \stdClass();
//        $obj->data = Tk_Url::createDataUrl($val);
//        $cval = $map->getColumnValue($obj);
//        vd($cval['data'], enquote($val));
//        $this->assertEquals($cval['data'], enquote($val));
//
//        $row = array('data' => $val, 'ItemMisc' => 'test');
//        vd($row);
//        $pval = $map->getPropertyValue($row);
//
//        $this->assertEquals($pval->toString(), $obj->data->getPath());

    }

    /**
     *
     */
    public function testColorMap()
    {

        $map = Map\Color::create('data');
        $this->assertEquals($map->getPropertyName(), 'data');
        $arr = $map->getColumnNames();
        $this->assertEquals(current($arr), 'data');

        $val = \Tk\Color::create('FFEB87');

        $obj = new \stdClass();
        $obj->data = $val;
        $cval = $map->getColumnValue($obj);
        $this->assertEquals($cval['data'], enquote($val->toString()));

        $row = array('data' => $val->toString(), 'ItemMisc' => 'test');
        $pval = $map->getPropertyValue($row);

        $this->assertEquals($pval->toString(), $val->toString());

    }


    /**
     *
     */
    public function testDateMap()
    {

        $map = Map\Date::create('data');
        $this->assertEquals($map->getPropertyName(), 'data');
        $arr = $map->getColumnNames();
        $this->assertEquals(current($arr), 'data');

        $val = \Tk\Date::create();

        $obj = new \stdClass();
        $obj->data = $val;
        $cval = $map->getColumnValue($obj);
        $this->assertEquals($cval['data'], enquote($val->toString()));

        $row = array('data' => $val->toString(), 'ItemMisc' => 'test');
        $pval = $map->getPropertyValue($row);

        $this->assertEquals($pval->getTimestamp(), $val->getTimestamp());

    }



    /**
     *
     */
    public function testMoneyMap()
    {

        $map = Map\Money::create('data');
        $this->assertEquals($map->getPropertyName(), 'data');
        $arr = $map->getColumnNames();
        $this->assertEquals(current($arr), 'data');

        $val = \Tk\Money::create(1000);

        $obj = new \stdClass();
        $obj->data = $val;
        $cval = $map->getColumnValue($obj);
        $this->assertEquals($cval['data'], $val->getAmount());

        $row = array('data' => $val->getAmount(), 'ItemMisc' => 'test');
        $pval = $map->getPropertyValue($row);

        $this->assertEquals($pval->getAmount(), $val->getAmount());

    }

}

