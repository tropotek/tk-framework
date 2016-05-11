<?php
namespace Tk;


/**
 * Class Encrypt
 * 
 * An object to handle string encryption based on a key
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Encrypt
{
    /**
     * The default key if none entered
     * @var string
     */
    static $key = '@@_Default_TK_@@';

    /**
     *  encrypt
     *
     * @param string $string
     * @param string $key
     * @return string
     */
    static function encode($string, $key = '')
    {
        if ($key == '') {
            $key = self::$key;
        }
        $result = '';
        for($i = 0; $i < strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key)) - 1, 1);
            $char = chr(ord($char) + ord($keychar));
            $result .= $char;
        }
        return base64_encode($result);
    }

    /**
     * decrypt
     *
     * @param string $string
     * @param string $key
     * @return string
     */
    static function decode($string, $key = '')
    {
        if ($key == '') {
            $key = self::$key;
        }
        $result = '';
        $string = base64_decode($string);
        for($i = 0; $i < strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key)) - 1, 1);
            $char = chr(ord($char) - ord($keychar));
            $result .= $char;
        }
        return $result;
    }

}

