<?php
namespace Tk;

/**
 * An object filled with string utility methods.
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Hash
{

    const MD5 = 'md5';
    const SHA1 = 'sha1';
    const CRC32 = 'crc32';


    /**
     * Return a list of registered hashing algorithms
     * @return array
     */
    public static function getAllAlgorithms()
    {
        return hash_algos();
    }

    /**
     * @param string $algo
     * @param string $str
     * @param bool $raw_output
     * @return string
     */
    public static function hash($algo, $str, $raw_output = false)
    {
        return hash($algo, $str, $raw_output);
    }

    /**
     * @param string $algo
     * @param string $filename
     * @param bool $raw_output
     * @return string
     */
    public static function hashFile($algo, $filename, $raw_output = false)
    {
        return hash_file($algo, $filename, $raw_output);
    }

    /**
     * @param string $str
     * @param bool $raw_output
     * @return string
     */
    public static function md5($str, $raw_output = false)
    {
        return self::hash(self::MD5, $str, $raw_output);
    }

    /**
     * @param string $str
     * @param bool $raw_output
     * @return string
     */
    public static function sha1($str, $raw_output = false)
    {
        return self::hash(self::SHA1, $str, $raw_output);
    }

    /**
     * @param string $str
     * @param bool $raw_output
     * @return string
     */
    public static function crc32($str, $raw_output = false)
    {
        return self::hash(self::CRC32, $str, $raw_output);
    }

}