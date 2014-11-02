<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Test;

use \Tk\Exception as Exception;
/**
 *
 * @package Tk\Test
 */
class ExceptionTest extends \PHPUnit_Framework_TestCase
{

    public function __construct()
    {
        parent::__construct('Sys Exception Test');
    }

    public function setUp()
    {

    }

    public function tearDown()
    {

    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Test Message
     */
    public function testExceptionMessage()
    {
        $this->setExpectedException(
          'Exception', 'Test Message'
        );
        throw new Exception('Test Message', 1001);
    }

    /**
     * @expectedException     Exception
     * @expectedExceptionCode 1001
     */
    public function testExceptionCode()
    {
        $this->setExpectedException(
          'Exception', 'Test Message', 1001
        );
        throw new Exception('Test Message', 1001);
    }


}

