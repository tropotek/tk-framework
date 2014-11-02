<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Test;

use \Tk\Db\Tool as Tool;

/**
 *
 * @package Tk\Test
 */
class ToolTest extends \PHPUnit_Framework_TestCase
{

    public function __construct()
    {
        parent::__construct('Db Tool Test');
    }

    public function setUp()
    {

    }

    public function tearDown()
    {

    }


    /**
     *
     *
     */
    public function testCreate()
    {
        $tool = Tool::create('`created`', 5, 0, 40);
        $this->assertEquals($tool->getPageNo(), 1, 'Page 1 Expected');
    }


    /**
     *
     *
     */
    public function testPage()
    {
        $tool = Tool::create('`created`', 5, 0, 40);
        $tool->setOffset(10);
        $this->assertEquals($tool->getPageNo(), 3, 'Page 3 expected');
        $this->assertEquals($tool->getOrderBy(), '`created`', 'Order by `created` expected');
        $this->assertEquals($tool->getTotal(), 40, 'Count of 40 expected');
        $this->assertEquals($tool->getOffset(), 10, 'Offset of 10 expected');
    }

}

