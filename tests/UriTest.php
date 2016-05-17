<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Test;

use \Tk\Uri;
/**
 *
 * @package Tk\Test
 */
class UriTest extends \PHPUnit_Framework_TestCase
{

    public function __construct()
    {
        parent::__construct('\Tk\Uri Test');
    }

    public function setUp()
    {
        
    }

    public function tearDown()
    {

    }


    public function testComponents()
    {
        $url = new Uri('http://www.example.com/test.html?y=1&x=2#middle');
        $this->assertEquals($url->getScheme(), 'http');
        $this->assertEquals($url->getHost(), 'www.example.com');
        $this->assertEquals($url->getPath(), '/test.html');
        $this->assertEquals($url->getQuery(), 'y=1&x=2');
        $this->assertEquals($url->get('x'), '2');
        $this->assertEquals($url->getFragment(), 'middle');

        $url->set('z', '3');
        $this->assertEquals($url->getQuery(), 'y=1&x=2&z=3');
    }

    public function testPathPrefix()
    {
        $host = 'localhost';
        $prepend = '/~user/tests';
        $specs = array(
            'http://www.example.com/' => 'http://www.example.com/',
            '/test.html' =>  'http://' . $host . '/~user/tests/test.html',
            './test.html' => 'http://' . $host . '/~user/tests/./test.html',
            'test.html' =>   'http://' . $host . '/~user/tests/test.html',
        );

        foreach ($specs as $spec => $strUrl) {
            Uri::$BASE_URL_PATH = $prepend;
            $url = new Uri($spec);
            $this->assertEquals($url->toString(), $strUrl, 'In Url: ' . $strUrl . ' - Prepend: ' . $prepend . ' - Out Url: ' . $url->toString());
        }
    }

    public function testQuery()
    {
        $url = new Uri('http://www.example.com');
        $url->set('x', ';:+.% ~#|');
        $url->set('y', 'testing 1 2 3');
        $url->set('z', 'http://www.example.com');
        $this->assertEquals($url->getQuery(), 'x=%3B%3A%2B.%25+%7E%23%7C&y=testing+1+2+3&z=http%3A%2F%2Fwww.example.com');
        $this->assertEquals($url->get('x'), ';:+.% ~#|');
        $this->assertEquals($url->get('y'), 'testing 1 2 3');
        $this->assertEquals($url->get('z'), 'http://www.example.com');
    }

    public function testToString()
    {
        Uri::$BASE_URL_PATH = '';
        $host = 'localhost';
        $specs = array(
            // TODO: We need to find a way to pass these types of examples.
//            'www.example.com/test/path' => 'http://www.example.com/test/path',
//            'www.example.com' => 'http://www.example.com',
//            'ftp.example.com' => 'http://ftp.example.com',
            
            'http://www.example.com' => 'http://www.example.com',
            'https://www.example.com'  => 'https://www.example.com',
            'http://www.example.com/test.html' => 'http://www.example.com/test.html',
            'http://www.example.com/test.html?y=1&x=2' => 'http://www.example.com/test.html?y=1&x=2',
            'http://www.example.com/test.html?y=1&x=2#middle' => 'http://www.example.com/test.html?y=1&x=2#middle',
            'http://www.example.com/test.html#middle' => 'http://www.example.com/test.html#middle',
            '/test.html' => 'http://' . $host . '/test.html',
            './test.html' => 'http://' . $host . '/./test.html',
            'test.html' => 'http://' . $host . '/test.html',
        );

        foreach ($specs as $spec => $strUrl) {
            $url = new Uri($spec);
            $this->assertEquals($strUrl, $url->toString());
        }
    }

}

