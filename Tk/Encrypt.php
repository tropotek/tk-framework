<?php
namespace Tk;


/**
 * Class Encrypt
 * 
 * An object to handle string encryption based on a key
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Encrypt
{
    /**
     * @var string
     */
    private $key = '@@_Default_TK_@@';

    /**
     * Encrypt constructor.
     *
     * @param string $key
     */
    public function __construct($key = null)
    {
        if ($key)
            $this->key = $key;
    }

    /**
     *
     * @param string $key
     * @return Encrypt
     */
    public static function create($key = null)
    {
        return new self($key);
    }

    /**
     *  encrypt
     *
     * @param string $string
     * @return string
     */
    public function encode($string)
    {
        $result = '';
        for($i = 0; $i < strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($this->key, ($i % strlen($this->key)) - 1, 1);
            $char = chr(ord($char) + ord($keychar));
            $result .= $char;
        }
        return base64_encode($result);
    }

    /**
     * decrypt
     *
     * @param string $string
     * @return string
     */
    public function decode($string)
    {
        $result = '';
        $string = base64_decode($string);
        for($i = 0; $i < strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($this->key, ($i % strlen($this->key)) - 1, 1);
            $char = chr(ord($char) - ord($keychar));
            $result .= $char;
        }
        return $result;
    }

}

