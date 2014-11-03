<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Test;

use \Tk\Filesystem\Filesystem as Filesystem;
/**
 *
 * @package TkTest
 */
class FilesystemTest extends \PHPUnit_Framework_TestCase
{

    public function __construct()
    {
        parent::__construct('Filesystem Test');
    }

    public function setUp()
    {

    }

    public function tearDown()
    {

    }


    /**
     * Test the local filesystem adaptor
     *
     */
    public function testLocal()
    {
        $fs = new Filesystem(new \Tk\Filesystem\Adapter\Local(\Tk\Config::getInstance()->getDataPath()), \Tk\Config::getInstance()->getSitePath());

        $dest = '/test';

        $fs->mkdir($dest);
        $val = $fs->isDir($dest);
        $this->assertTrue($val, 'Check of is_dir');

        $fs->putTree('/vendor/ttek/tk-framework/Tk', $dest);
        $val = $fs->isFile($dest . '/Url.php');
        $this->assertTrue($val, 'Check if Url.php file got copied');

        $fs->rmTree($dest);
        $val = $fs->isDir($dest);
        $this->assertFalse($val, 'Check all got deleted ok');

        $fs->close();
    }


    /**
     * Test the FTP filesystem adaptor
     *
     */
    public function testFtp()
    {
//        $ad = new \Tk\Filesystem\Adapter\Ftp('dev.ttek.org', 'username', 'password',
//              str_replace('/home/path', '', dirname(\Tk\Config::getInstance()->getSitePath()) ));
//        $fs = new Filesystem($ad, dirname(\Tk\Config::getInstance()->getSitePath()));
//
//        $dest = '/data/tmp/ftp2';
//
//        $fs->mkdir($dest);
//        $val = $fs->isDir($dest);
//        $this->assertTrue($val, 'Check of dir created');
//
//        $fs->putTree('/lib/Tk/Filesystem', $dest);
//        $val = $fs->isFile($dest . '/Interface.php');
//        $this->assertTrue($val, 'Check if Interface.php file got copied');
//
//        $fs->rmTree($dest);
//        $val = $fs->isDir($dest);
//        $this->assertFalse($val, 'Check all got deleted ok');
//
//        $fs->close();
    }



}

