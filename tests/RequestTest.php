<?php
namespace tests;

use \Tk\Request as Request;

/**
 * Class RequestTest
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Request
     */
    public $request = null;

    public function __construct()
    {
        parent::__construct('Request Test');
        $this->request = new Request();

    }

    public function setUp()
    {

    }

    public function tearDown()
    {

    }



    public function testGetSetExists()
    {
        $this->request->set('test.val1', 'test');
        $this->assertEquals($this->request->get('test.val1'), 'test');
        $this->assertTrue($this->request->exists('test.val1'));
        $this->request->set('test.val1', null);
        $this->assertFalse($this->request->exists('test.val1'));
    }

    public function testGetAll()
    {
        $this->assertTrue(is_array($this->request->getAll()), 'Request returns an array');
    }

    public function testGetReferer()
    {
        $_SERVER['HTTP_REFERER'] = 'http://dev.example.com/test1.html';
        $this->assertEquals(get_class($this->request->getReferer()), 'Tk\Uri');
        $this->assertEquals($this->request->getReferer()->toString(), 'http://dev.example.com/test1.html');
    }

    public function testGetRemoteIp()
    {
        if (substr(php_sapi_name(), 0, 3) == 'cli') {
            $this->markTestSkipped('CLI Remote IP unavailable');
            return;
        }
        $this->assertTrue(preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $this->request->getRemoteAddr()) == 1);
    }
    
}

