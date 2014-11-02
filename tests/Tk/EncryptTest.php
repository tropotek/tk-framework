<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Test;

use \Tk\Encrypt as Encrypt;
/**
 *
 * @package Tk\Test
 */
class EncryptTest extends \PHPUnit_Framework_TestCase
{
    public $text = 'This is an un-encrypted string';
    public $eyncryptedText = 'lKip0mTO2YHW2pTUwnjErqOyuc+4ysqB6ODmyMKy';



    public function __construct()
    {
        parent::__construct('Sys Encypt Test');
    }

    public function setUp()
    {

    }

    public function tearDown()
    {

    }

    public function testEncrypt()
    {
        $str = Encrypt::encode($this->text);
        $this->assertEquals($str, $this->eyncryptedText, 'encode()');
    }

    public function testDecrypt()
    {
        $str = Encrypt::decode($this->eyncryptedText);
        $this->assertEquals($this->text, $str, 'decode()');
    }


}

