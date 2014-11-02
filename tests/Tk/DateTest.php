<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Test;

use \Tk\Date as Date;
use \Tk\Config as Config;
/**
 *
 * @package Tk\Test
 */
class DateTest extends \PHPUnit_Framework_TestCase
{

    protected $ts = 1319517680;
//        (Date): Date Object
//        (
//            [date] => 2011-10-25 14:41:20
//            [timezone_type] => 3
//            [timezone] => Australia/Queensland
//        )

    public function __construct()
    {
        parent::__construct('Date Test');

    }

    public function setUp()
    {

    }

    public function tearDown()
    {

    }


    /**
     * Test
     *
     */
    public function testCreate()
    {
        $d1 = Date::create($this->ts);
        $this->assertEquals($d1->getYear(), 2011);
        $this->assertEquals($d1->getMonth(), 10);
        $this->assertEquals($d1->getDate(), 25);

        $d1 = Date::create('2011-10-25 14:41:20');
        $this->assertEquals($d1->getYear(), 2011);
        $this->assertEquals($d1->getMonth(), 10);
        $this->assertEquals($d1->getDate(), 25);
        $this->assertEquals($d1->toString(), '2011-10-25 14:41:20');

    }

    /**
     * Test
     *
     */
    public function testCompareDates()
    {
        $d1 = Date::create($this->ts);
        $d2 = Date::create('2011-09-25 14:41:20');
        $d3 = Date::create('2011-09-25 14:41:20');

        $this->assertTrue($d2 < $d1);
        $this->assertTrue($d1 > $d2);
        $this->assertTrue($d1 != $d2);
        $this->assertTrue($d2 == $d3);


        $this->assertEquals(Date::getMonthDays(2, 1999), 28);
        $this->assertEquals(Date::getMonthDays(2, 2000), 29);

        $this->assertFalse(Date::isLeapYear(1999));
        $this->assertTrue(Date::isLeapYear(2000));

    }

    /**
     * Test
     *
     */
    public function testTimezone()
    {
        $d1 = Date::create($this->ts, new \DateTimeZone('Australia/Queensland'));
        $d2 = $d1->getUTCDate();
        $this->assertEquals($d1->getOffset()/(60*60), 10);
        $this->assertEquals($d2->getHour(), 4);

    }

    /**
     * Test
     *
     */
    public function testTime()
    {
        //[date] => 2011-10-25 14:41:20
        $d1 = Date::create($this->ts);

        $d1 = $d1->floor();
        $this->assertEquals($d1->format('H:i:s'), '00:00:00');

        $d1 = $d1->ceil();
        $this->assertEquals($d1->format('H:i:s'), '23:59:59');

    }

    /**
     * Test
     *
     */
    public function testAddition()
    {
        $d1 = Date::create($this->ts);
        $d2 = $d1->getUTCDate();

        $d1 = $d1->floor()->addSeconds(120);
        $this->assertEquals($d1->format('H:i:s'), '00:02:00');

        $d1 = $d1->floor()->addDays(2);
        $this->assertEquals($d1->format('Y-m-d'), '2011-10-27');

        $d1 = $d1->floor()->addMonths(2);
        $this->assertEquals($d1->format('Y-m-d'), '2011-12-27');

        $d1 = $d1->floor()->addYears(2);
        $this->assertEquals($d1->format('Y-m-d'), '2013-12-27');

    }

    /**
     * Test
     *
     */
    public function testDifference()
    {
        $d1 = Date::create($this->ts)->floor();
        $d2 = $d1->addDays(2);
        $diff = $d2->dayDiff($d1);
        $this->assertEquals($diff, 2);

        $d1 = Date::create($this->ts)->floor();
        $d2 = $d1->addSeconds(60*60*2);
        $diff = $d2->hourDiff($d1);
        $this->assertEquals($diff, 2);

    }

    /**
     * Test
     *
     */
    public function testSerialise()
    {
        $d = Date::create('2011-09-25 14:41:20');
        $this->assertEquals($d->toString(), '2011-09-25 14:41:20');

        $str = serialize($d);
        $d2 = unserialize($str);

        $this->assertEquals($d2->toString(), '2011-09-25 14:41:20');
        //$this->assertEquals($d2->getTimezone()->getName(), Config::getInstance()->get('system.timezone'));


    }

}

