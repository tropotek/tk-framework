<?php
namespace tests;

use \Tk\Config as Config;

/**
 * Class ConfigTest
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{


    public function __construct()
    {
        parent::__construct('Config Test');
    }

    public function setUp()
    {

    }

    public function tearDown()
    {

    }



    public function testCreate()
    {
        $config = Config::getInstance();
        $this->assertTrue($config instanceof Config);
    }

    public function testGetSet()
    {

        $config = Config::getInstance();
        $this->assertStringStartsWith($config->getSitePath(), dirname(__FILE__));
    }

    public function testNset()
    {
        $config = Config::getInstance();
        //$config->set('test.var1', 'success');
        $config['test.var1'] = 'success';
        $this->assertEquals($config->get('test.var1'), 'success');
        //$config->set('test.var1', 'overwrite');
        $config['test.var1'] = 'overwrite';
        $this->assertEquals($config->get('test.var1'), 'overwrite');

        $this->assertEquals($config->has('test.var2'), false);
        $config->set('test.var2', 'test');
        $this->assertEquals($config->has('test.var2'), true);

    }

}

