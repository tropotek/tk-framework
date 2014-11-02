<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Test;

use \Tk\Response as Response;
/**
 *
 * @package Tk\Test
 */
class ResponseTest extends \PHPUnit_Framework_TestCase
{

    public function __construct()
    {
        parent::__construct('Sys Response Test');
    }

    public function setUp()
    {

    }

    public function tearDown()
    {

    }



    public function testCreate()
    {
        $res = new Response();
        $res->write('testing');

        $this->assertEquals($res->toString(), 'testing');
    }

    public function testObject()
    {
        $res = new Response();
        $res->write('testing');

        $this->assertEquals($res->toString(), 'testing');
    }

}

