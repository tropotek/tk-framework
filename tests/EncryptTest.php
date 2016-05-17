<?php
namespace tests;

use \Tk\Encrypt as Encrypt;

/**
 * Class EncryptTest
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class EncryptTest extends \PHPUnit_Framework_TestCase
{
    public $text = 'This is an un-encrypted string';
    public $eyncryptedText = 'lKip0mTO2YHW2pTUwnjErqOyuc+4ysqB6ODmyMKy';



    public function __construct()
    {
        parent::__construct('Encypt Test');
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

