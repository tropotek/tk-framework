<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Test;

use \Tk\Log\Log as Log;

/**
 *
 * @package Tk\Test
 */
class LogTest extends \PHPUnit_Framework_TestCase
{
    private $orgLog = '';
    private $orgLvl = 1;
    private $testLog = '';


    public function __construct()
    {
        parent::__construct('Sys Log Test');
//        $this->testLog = Tk_Config::getInstance()->get('system.dataPath') . '/UnitTests/data/utest.txt';
    }

    public function setUp()
    {
//        if (!is_writable(Tk_Config::getInstance()->get('system.dataPath'))) {
//            throw new Tk_Exception('Connot write to Data path: ' . Tk_Config::getInstance()->get('system.dataPath'));
//        }
//        $this->orgLog = Tk_Config::getInstance()->get('system.log');
//        $this->orgLvl = Tk_Config::getInstance()->get('system.logLevel');
//
//        Tk_Config::getInstance()->set('system.logLevel', 5);
//        Tk_Config::getInstance()->set('system.log', $this->testLog);
//        file_put_contents($this->testLog, '');

    }

    public function tearDown()
    {
//        Tk_Config::getInstance()->set('system.log', $this->orgLog);
//        @unlink($this->testLog);
    }



    public function testCreate()
    {
//        Tk_Log::write('test');
//        $buf = file_get_contents($this->testLog);
//        $this->assertTrue(preg_match('/test$/', $buf));

    }


    public function testLog()
    {
//        Tk_Config::getInstance()->set('system.logLevel', 2);
//        Tk_Log::write('not write');
//        $this->assertTrue(preg_match('/test$/', $buf));

    }

}

