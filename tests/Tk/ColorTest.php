<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Test;

use \Tk\Color as Color;

/**
 *
 * @package Tk\Test
 */
class ColorTest extends \PHPUnit_Framework_TestCase
{

    public function __construct()
    {
        parent::__construct('Color Test');
    }

    public function setUp()
    {

    }

    public function tearDown()
    {

    }

    public function testCreate()
    {
        $c = Color::create('FFFFFF');
        $this->assertEquals($c->getName(), 'White');

        $c = Color::createDecimal(255, 255, 255);
        $this->assertEquals($c->getName(), 'White');

        $c = Color::create('000');
        $this->assertEquals($c->getName(), 'Black');

        $c = Color::create('000');
        $this->assertEquals($c->toString(), '000000');

    }

    public function testDecimalColor()
    {
        $c = Color::create('6699CC');
        $this->assertEquals($c->getRed(), 102);
        $this->assertEquals($c->getGreen(), 153);
        $this->assertEquals($c->getBlue(), 204);

        $c = $c->getInverse();
        $this->assertEquals($c->getRed(), 153);
        $this->assertEquals($c->getGreen(), 102);
        $this->assertEquals($c->getBlue(), 51);
        $this->assertEquals($c->toString(), '996633');
    }


}

