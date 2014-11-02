<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Test;

use \Tk\Request as Request;
/**
 *
 * @package Tk\Test
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Request
     */
    public $request = null;

    public function __construct()
    {
        parent::__construct('Sys Request Test');
        $this->request = Request::getInstance();

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
        $this->assertEquals(get_class($this->request->getReferer()), 'Tk\Url');
        $this->assertEquals($this->request->getReferer()->toString(), 'http://dev.example.com/test1.html');
    }

    public function testGetRemoteIp()
    {
        if (IS_CLI) {
            $this->markTestSkipped(
              'CLI Remote IP unavailable'
            );
            return;
        }
        $this->assertTrue(preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $this->request->getRemoteAddr()) == 1);
    }


    public function testCookies()
    {
        $key = 'test.cookie';
        $val = 'lala lala';

        $this->request->setCookie($key, $val);
        //$this->assertEquals($this->request->getCookie($key), $val);
        //$this->assertTrue($this->request->cookieExists($key));
        $this->request->deleteCookie($key);
        $this->assertFalse($this->request->cookieExists($key));
    }






}

