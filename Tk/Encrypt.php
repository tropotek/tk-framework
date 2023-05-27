<?php
namespace Tk;

/**
 * An object to handle basic string encryption based on a secret key
 */
class Encrypt
{
    /**
     * This key needs to be the same to encrypt and decrypt a value.
     */
    private string $secret;


    public function __construct(string $secret)
    {
        $this->secret = $secret;
    }

    public static function create(string $secret): Encrypt
    {
        return new self($secret);
    }

    public function encrypt(string $string): string
    {
        $result = '';
        for($i = 0; $i < strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($this->secret, ($i % strlen($this->secret)) - 1, 1);
            $char = chr(ord($char) + ord($keychar));
            $result .= $char;
        }
        return base64_encode($result);
    }

    public function decrypt(string $string): string
    {
        $result = '';
        $string = base64_decode($string);
        for($i = 0; $i < strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($this->secret, ($i % strlen($this->secret)) - 1, 1);
            $char = chr(ord($char) - ord($keychar));
            $result .= $char;
        }
        return $result;
    }

}

