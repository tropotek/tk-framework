<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk;

/**
 * This class is used to manage an RGB color type
 *
 *
 * @package Tk
 */
class Color extends Object
{

    /**
     * A complete list of the color names supported by all major browsers.
     *
     * @var array
     */
    static $colorChart = array(
    	'AliceBlue' => 'F0F8FF', 'AntiqueWhite' => 'FAEBD7',
    	'Aqua' => '00FFFF', 'Aquamarine' => '7FFFD4',
    	'Azure' => 'F0FFFF', 'Beige' => 'F5F5DC',
    	'Bisque' => 'FFE4C4', 'Black' => '000000',
    	'BlanchedAlmond' => 'FFEBCD', 'Blue' => '0000FF',
    	'BlueViolet' => '8A2BE2', 'Brown' => 'A52A2A',
    	'BurlyWood' => 'DEB887', 'CadetBlue' => '5F9EA0',
    	'Chartreuse' => '7FFF00', 'Chocolate' => 'D2691E',
    	'Coral' => 'FF7F50', 'CornflowerBlue' => '6495ED',
    	'Cornsilk' => 'FFF8DC', 'Crimson' => 'DC143C',
    	'Cyan' => '00FFFF', 'DarkBlue' => '00008B',
    	'DarkCyan' => '008B8B', 'DarkGoldenRod' => 'B8860B',
    	'DarkGray' => 'A9A9A9', 'DarkGreen' => '006400',
    	'DarkKhaki' => 'BDB76B', 'DarkMagenta' => '8B008B',
    	'DarkOliveGreen' => '556B2F', 'Darkorange' => 'FF8C00',
    	'DarkOrchid' => '9932CC', 'DarkRed' => '8B0000',
    	'DarkSalmon' => 'E9967A', 'DarkSeaGreen' => '8FBC8F',
    	'DarkSlateBlue' => '483D8B', 'DarkSlateGray' => '2F4F4F',
    	'DarkTurquoise' => '00CED1', 'DarkViolet' => '9400D3',
    	'DeepPink' => 'FF1493', 'DeepSkyBlue' => '00BFFF',
    	'DimGray' => '696969', 'DodgerBlue' => '1E90FF',
    	'FireBrick' => 'B22222', 'FloralWhite' => 'FFFAF0',
    	'ForestGreen' => '228B22', 'Fuchsia' => 'FF00FF',
    	'Gainsboro' => 'DCDCDC', 'GhostWhite' => 'F8F8FF',
    	'Gold' => 'FFD700', 'GoldenRod' => 'DAA520',
    	'Gray' => '808080', 'Green' => '008000',
    	'GreenYellow' => 'ADFF2F', 'HoneyDew' => 'F0FFF0',
    	'HotPink' => 'FF69B4', 'IndianRed' => 'CD5C5C',
    	'Indigo' => '4B0082', 'Ivory' => 'FFFFF0',
    	'Khaki' => 'F0E68C', 'Lavender' => 'E6E6FA',
    	'LavenderBlush' => 'FFF0F5', 'LawnGreen' => '7CFC00',
    	'LemonChiffon' => 'FFFACD', 'LightBlue' => 'ADD8E6',
    	'LightCoral' => 'F08080', 'LightCyan' => 'E0FFFF',
    	'LightGoldenRodYellow' => 'FAFAD2', 'LightGrey' => 'D3D3D3',
    	'LightGreen' => '90EE90', 'LightPink' => 'FFB6C1',
    	'LightSalmon' => 'FFA07A', 'LightSeaGreen' => '20B2AA',
    	'LightSkyBlue' => '87CEFA', 'LightSlateGray' => '778899',
    	'LightSteelBlue' => 'B0C4DE', 'LightYellow' => 'FFFFE0',
    	'Lime' => '00FF00', 'LimeGreen' => '32CD32',
    	'Linen' => 'FAF0E6', 'Magenta' => 'FF00FF',
    	'Maroon' => '800000', 'MediumAquaMarine' => '66CDAA',
    	'MediumBlue' => '0000CD', 'MediumOrchid' => 'BA55D3',
    	'MediumPurple' => '9370D8', 'MediumSeaGreen' => '3CB371',
    	'MediumSlateBlue' => '7B68EE', 'MediumSpringGreen' => '00FA9A',
    	'MediumTurquoise' => '48D1CC', 'MediumVioletRed' => 'C71585',
    	'MidnightBlue' => '191970', 'MintCream' => 'F5FFFA',
    	'MistyRose' => 'FFE4E1', 'Moccasin' => 'FFE4B5',
    	'NavajoWhite' => 'FFDEAD', 'Navy' => '000080',
    	'OldLace' => 'FDF5E6', 'Olive' => '808000',
    	'OliveDrab' => '6B8E23', 'Orange' => 'FFA500',
    	'OrangeRed' => 'FF4500', 'Orchid' => 'DA70D6',
    	'PaleGoldenRod' => 'EEE8AA', 'PaleGreen' => '98FB98',
    	'PaleTurquoise' => 'AFEEEE', 'PaleVioletRed' => 'D87093',
    	'PapayaWhip' => 'FFEFD5', 'PeachPuff' => 'FFDAB9',
    	'Peru' => 'CD853F', 'Pink' => 'FFC0CB', 'Plum' => 'DDA0DD',
    	'PowderBlue' => 'B0E0E6', 'Purple' => '800080',
    	'Red' => 'FF0000', 'RosyBrown' => 'BC8F8F',
    	'RoyalBlue' => '4169E1', 'SaddleBrown' => '8B4513',
    	'Salmon' => 'FA8072', 'SandyBrown' => 'F4A460',
    	'SeaGreen' => '2E8B57', 'SeaShell' => 'FFF5EE',
    	'Sienna' => 'A0522D', 'Silver' => 'C0C0C0',
    	'SkyBlue' => '87CEEB', 'SlateBlue' => '6A5ACD',
    	'SlateGray' => '708090', 'Snow' => 'FFFAFA',
    	'SpringGreen' => '00FF7F', 'SteelBlue' => '4682B4',
    	'Tan' => 'D2B48C', 'Teal' => '008080',
    	'Thistle' => 'D8BFD8', 'Tomato' => 'FF6347',
    	'Turquoise' => '40E0D0', 'Violet' => 'EE82EE',
    	'Wheat' => 'F5DEB3', 'White' => 'FFFFFF',
    	'WhiteSmoke' => 'F5F5F5', 'Yellow' => 'FFFF00',
    	'YellowGreen' => '9ACD32');

    /**
     * @var string
     */
    private $hex = '000000';


    /**
     * Create a colour object with rgb Hexidecimal values 000000 = Black, FFFFFF = White
     * Class also supports shorthand hex where FFC = FFFFCC
     * Also the standard name of the color can be supplied see self::$colorChart
     * If an invalid/null color is supplied then the color is set to black (000000)
     *
     * @param string $hex
     */
    public function __construct($color = '')
    {
        $this->setColor($color);
    }

    /**
     * Create Tk\Color from a hex string
     *
     * @param int $hex
     * @return Tk\Color
     */
    static function create($hex)
    {
        return new self($hex);
    }

    /**
     * Create Tk\Color from decimal values
     *
     * @param int $red
     * @param int $green
     * @param int $blue
     * @return Tk\Color
     */
    static function createDecimal($red = 0, $green = 0, $blue = 0)
    {
        $r = dechex($red);
        $g = dechex($green);
        $b = dechex($blue);
        $hex = sprintf('%02s%02s%02s', $r, $g, $b);
        return new self($hex);
    }

    /**
     * Set the color object's hex value
     *
     * @param string $str
     * @return Tk\Color
     */
    public function setColor($str)
    {
        $regs = null;
        if (preg_match('/^(\#)?([A-F0-9]{3})$/i', $str, $regs)) {
            $str = $regs[2][0] . $regs[2][0] . $regs[2][1] . $regs[2][1] . $regs[2][2] . $regs[2][2];
        }
        if (preg_match('/^(\#)?([A-F0-9]{6})$/i', $str, $regs)) {
            $this->hex = $regs[2];
            $this->hex = strtoupper($this->hex);
            return $this;
        }
        if (preg_match('/^([a-z]{3,32})$/i', $str) && array_key_exists($str, self::$colorChart)) {
            $this->hex = self::$colorChart[$str];
        }
        $this->hex = strtoupper($this->hex);
        return $this;
    }

    /**
     * Return the Colour name if available
     *
     * @return string
     */
    public function getName()
    {
        foreach (self::$colorChart as $c => $v) {
            if ($v == $this->hex) {
                return $c;
            }
        }
        return '';
    }

    /**
     * get decimal Red
     *
     * @return int
     */
    public function getRed()
    {
        return intval(substr($this->hex, 0, 2), 16);
    }

    /**
     * get decimal Green
     *
     * @return int
     */
    public function getGreen()
    {
        return intval(substr($this->hex, 2, 2), 16);
    }

    /**
     * get decimal Blue
     *
     * @return int
     */
    public function getBlue()
    {
        return intval(substr($this->hex, 4, 2), 16);
    }

    /**
     * Get the inverse color to this color.
     *
     * @return Tk/Color
     */
    public function getInverse()
    {
        return self::createDecimal(255 - $this->getRed(), 255 - $this->getGreen(), 255 - $this->getBlue());
    }

    /**
     * Return a value of this color object as a HEX value
     *
     * @return string
     */
    public function toString($hash = false)
    {
        if ($hash) {
            return '#'.$this->hex;
        }
        return $this->hex;
    }

}