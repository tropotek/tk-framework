<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Test;

use \Tk\Money as Money;
use Tk\Currency as Currency;
/**
 *
 * @package Tk\Test
 */
class MoneyTest extends \PHPUnit_Framework_TestCase
{

    public function __construct()
    {
        parent::__construct('Money Test');
    }

    public function setUp()
    {

    }

    public function tearDown()
    {

    }

    public function testCreate()
    {

        $m1 = Money::create(2200, Currency::getInstance('AUD'));
        $this->assertEquals($m1->getAmount(), 2200);


    }

    public function testObject()
    {

        $m1 = Money::create(2200, Currency::getInstance('AUD'));
        $m2 = Money::create(2200, Currency::getInstance('AUD'));
        $m3 = Money::create(2200, Currency::getInstance('NZD'));

        //$this->expectException();
        //$m4 = $m1->add($m3);

        $m4 = $m1->add($m2);
        $this->assertEquals($m4->getAmount(), 4400);

        $m4 = $m1->multiply(2);
        $this->assertEquals($m4->getAmount(), 4400);

        $m4 = $m1->divideBy(2);
        $this->assertEquals($m4->getAmount(), 1100);

        $m4 = $m1->subtract($m2);
        $this->assertEquals($m4->getAmount(), 0);


    }

    public function testConditionals()
    {

        $m1 = Money::create(1000, Currency::getInstance('AUD'));
        $m2 = Money::create(2200, Currency::getInstance('AUD'));
        $this->assertTrue($m1->lessThan($m2));
        $this->assertTrue($m2->greaterThan($m1));

        $this->assertEquals($m1->toString(), '$10.00');
        $this->assertEquals($m1->toFloatString(), '10.00');


    }


}

