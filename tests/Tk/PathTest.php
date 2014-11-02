<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Test;

use \Tk\Path as Path;

/**
 *
 * @package Tk\Test
 */
class PathTest extends \PHPUnit_Framework_TestCase
{

    public function __construct()
    {
        parent::__construct('Path Test');
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
        $path1 = Path::create('/home/project/test/file.tgz');
        $this->assertEquals($path1->toString(), '/home/project/test/file.tgz');
    }


    /**
     *
     *
     */
    public function testObject()
    {
        $path1 = Path::create('/home/test/path1');
        $path2 = Path::create('/path2/subdir/file');

        //$all = $path1->appendRepeat($path2);
        //$this->assertEquals($all->toString(), '/home/test/path1/path2/subdir/file');

        // TODO: Continue writing tests for static methods as well


    }

}

