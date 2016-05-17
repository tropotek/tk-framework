<?php
namespace tests;

use \Tk\Session;

/**
 * Class SessionTest
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class SessionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Session
     */
    protected $session = null;


    public function __construct()
    {
        parent::__construct('Session Test');
    }

    public function setUp()
    {
        $_SERVER['SERVER_NAME'] = 'localhost';
        $this->session = new Session(); 
    }

    public function tearDown()
    {

    }



    public function testCreate()
    {
        $this->assertInstanceOf('Tk\Session', $this->session);
        
        $this->session->set('test.var1', 'testtest');
        $t = $this->session->exists('test.var1');
        $this->assertTrue($t);


    }

    public function testObject()
    {
        $this->session->set('test.var2', 'testtest');
        $this->assertEquals($this->session->getOnce('test.var2'), 'testtest');
        $this->assertFalse($this->session->exists('test.var2'));
        
        // TODO Fix this test??? Not sure what is going on with the name???
        //$this->assertEquals(md5($_SERVER['SERVER_NAME']), $this->session->getName());


    }


}

