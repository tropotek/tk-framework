<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk;

/**
 * The Tk_Math object hadls Tk specific number functions
 *
 * rand(): Use this if you want seeded random numbers to work
 * reguardless of the suhosen patch....
 *
 * @link http://www.sitepoint.com/php-random-number-generator/#.T-4sKeEXthE
 * @package Tk
 */
class Math
{
    const PI = M_PI;
    const PI_2 = M_PI_2;        // pi/2
    const PI_4 = M_PI_4;        // pi/4


    private static $RSeed = 0;

    /**
     * Seed the random number generator
     *
     * @param int $s
     */
    static function seed($s = 0)
    {
        self::$RSeed = abs(intval($s)) % 9999999 + 1;
        self::rand();
    }

    /**
     * Generate a random number
     *
     * @param int $min
     * @param int $max
     * @return int
     */
    static function rand($min = 0, $max = 9999999)
    {
        if (self::$RSeed == 0) self::seed(mt_rand());
        self::$RSeed = (self::$RSeed * 125) % 2796203;
        return self::$RSeed % ($max - $min + 1) + $min;
    }

}
