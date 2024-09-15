<?php
namespace Tk;

/**
 * An object to manage and convert RGB colors
 *  o RGB
 *  o HSL
 *  o CYMK
 *  o ASCII Console codes
 */
class Color
{
    /**
     * Red Range [0-255]
     */
    private int $red;

    /**
     * Green Range [0-255]
     */
    private int $green;

    /**
     * Blue Range [0-255]
     */
    private int $blue;

    /**
     * Alpha or Opacity, Range [0.0-1.0]
     */
    private float $alpha;


    /**
     * Create RGB Color object
     *
     * Each number has a range of [0-255] Except alpha which has a range of [0.0 - 1.0]
     */
    public function __construct(int $red = 0, int $green = 0, int $blue = 0, float $alpha = 1.0)
    {
        $this->red = $red;
        $this->green = $green;
        $this->blue = $blue;
        $this->alpha = $alpha;
    }

    /**
     * Create RGB Color object
     *
     * The first parameter ($red) can be of the types:
     *  - int: for the decimal red portion of the color
     *  - Color: A Color object to copy, in which case the other params are ignored
     *  - hex: a 3-6 digit HEX color value in which case the other params are ignored
     *  - array: A 3-4 length array of red, green, blue, alpha decimal numbers (For static function calls self:Hsl2RGB())
     *
     * Each number has a range of [0-255] Except alpha which has a range of [0.0 - 1.0]
     *
     * @param int|array|Color|string $red If hex string or color object then the other params are ignored
     * @param int $green
     * @param int $blue
     * @param float $alpha
     * @return Color
     */
    public static function create($red = 0, int $green = 0, int $blue = 0, float $alpha = 1.0): Color
    {
        $color = new self();
        $color->setColor($red, $green, $blue, $alpha);
        return $color;
    }

    /**
     * Create a color object from HSL or HSV or HSB (Whatever you call it these days... ;-/ )
     *
     * @param float $hue
     * @param float $saturation
     * @param float $luminosity
     * @param float $alpha
     * @return Color
     */
    public static function createHsl(float $hue = 0.0, float $saturation = 0.0, float $luminosity = 0.0, float $alpha = 1.0): Color
    {
        $color = self::create(self::hsl2Rgb($hue, $saturation, $luminosity));
        $color->alpha = $alpha;
        return $color;
    }

    /**
     * Create a color object form CYMK color values
     *
     * @param int $cyan
     * @param int $yellow
     * @param int $magenta
     * @param int $key (alias for black)
     * @param float $alpha
     * @return Color
     */
    public static function createCymk(int $cyan = 0, int $yellow = 0, int $magenta = 0, int $key = 0, float $alpha = 1.0): Color
    {
        $color = self::create(self::cymk2Rgb($cyan, $yellow, $magenta, $key));
        $color->alpha = $alpha;
        return $color;
    }

    /**
     *
     */
    public static function createRandom(?int $seed = null, float $alpha = 1.0): Color
    {
        if (is_int($seed)) mt_srand($seed);
        $hex =
            str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT) .
            str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT) .
            str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT) ;
        $color = self::create($hex);
        $color->alpha = $alpha;
        return $color;
    }

    /**
     * Set the color object's value from a mix of available types
     *
     * The first parameter ($red) can be of the types:
     *  - int: for the decimal red portion of the color
     *  - Color: A Color object to copy, in which case the other params are ignored
     *  - hex: a 3-6 digit HEX color value in which case the other params are ignored
     *
     *
     * @param int|Color|string $red If hex string or color object then the other params are ignored
     */
    public function setColor($red, int $green = 0, int $blue = 0, float $alpha = 1.0): Color
    {
        if ($red instanceof Color) {
            $alpha = $red->getAlpha();
            $blue = $red->getBlue();
            $green = $red->getGreen();
            $red = $red->getRed();  // Last to avoid over-writing $red
        } else if (is_array($red) && count($red) <= 4) {
            $red = array_values($red);
            $alpha = isset($red[3]) ? $red[3] : 1;
            $blue = isset($red[2]) ? $red[2] : 0;
            $green = isset($red[1]) ? $red[1] : 0;
            $red = isset($red[0]) ? $red[0] : 0;
        } else if (is_string($red)) {
            try {
                list($red, $green, $blue) = array_values(self::hex2Rgb($red));
            } catch (\Exception $e) {
                $red = 0;
                $green = 0;
                $blue = 0;
                \Tk\Log::notice($e->__toString());
            }
        }
        // Assign color values
        $this->red = (int)$red;
        $this->green = (int)$green;
        $this->blue = (int)$blue;
        $this->alpha = (int)$alpha;
        return $this;
    }

    /**
     *
     */
    public function getHex(): string
    {
        return sprintf('%02s%02s%02s', dechex($this->getRed()), dechex($this->getGreen()), dechex($this->getBlue()));
    }

    /**
     * Outputs a pleasant text color assuming this color is used as a background
     *
     * @source https://24ways.org/2010/calculating-color-contrast/
     */
    public function getTextColor(): Color
    {
        $yiq = (($this->getRed()*299)+($this->getGreen()*587)+($this->getBlue()*114))/1000;
	    $hex = ($yiq >= 128) ? '#000000' : '#FFFFFF';
        return self::create($hex);
    }

    /**
     * Converts RGB color to HSL color
     *
     * Check http://en.wikipedia.org/wiki/HSL_and_HSV#Hue_and_chroma for details
     *   Output: Array(Hue, Saturation, Lightness) - Values from 0 to 1
     *
     * Aliases HSV, HSL, HSB
     */
    public function getHsl(): array
    {
        $r = $this->getRed();
        $g = $this->getGreen();
        $b = $this->getBlue();

        $r /= 255;
        $g /= 255;
        $b /= 255;
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $h = 0;
        $s = 0;
        $l = ($max + $min) / 2;
        $d = $max - $min;
        if ($d == 0) {
            $h = $s = 0; // achromatic
        } else {
            $s = $d / (1 - abs(2 * $l - 1));
            switch ($max) {
                case $r:
                    $h = 60 * fmod((($g - $b) / $d), 6);
                    if ($b > $g) {
                        $h += 360;
                    }
                    break;
                case $g:
                    $h = 60 * (($b - $r) / $d + 2);
                    break;
                case $b:
                    $h = 60 * (($r - $g) / $d + 4);
                    break;
            }
        }
        $h = round($h, 2);
        $s = round($s, 2);
        $l = round($l, 2);
        return ['hue' => $h, 'saturation' => $s, 'brightness' => $l];
    }

    /**
     * Return an array of CYMK values
     */
    public function getCymk(): array
    {
        $cyan = 255 - $this->getRed();
        $magenta = 255 - $this->getGreen();
        $yellow = 255 - $this->getBlue();
        $black = min($cyan, $magenta, $yellow);
        $cyan = @(($cyan - $black) / (255 - $black));
        $magenta = @(($magenta - $black) / (255 - $black));
        $yellow = @(($yellow - $black) / (255 - $black));
        return ['cyan' => $cyan, 'yellow' => $yellow, 'magenta' => $magenta, 'key' => $black];
    }

    /**
     * Get decimal Red
     */
    public function getRed(): int
    {
        return $this->red;
    }

    /**
     * Get decimal Green
     */
    public function getGreen(): int
    {
        return $this->green;
    }

    /**
     * Get decimal Blue
     */
    public function getBlue(): int
    {
        return $this->blue;
    }

    /**
     * Get the alpha chanel or opacity value
     * Range [0.0 - 1.0]
     */
    public function getAlpha(): float
    {
        return $this->alpha;
    }


    /**
     * Get the inverse color to this color.
     */
    public function getInverse(): Color
    {
        return self::create(255 - $this->getRed(), 255 - $this->getGreen(), 255 - $this->getBlue());
    }

    /**
     * Return the Colour name if available
     */
    public function getName(): string
    {
        foreach (self::$colorChart as $c => $v) {
            if ($v == $this->getHex()) {
                return $c;
            }
        }
        return '';
    }


    /**
     * Create Tk\Color from a hex string eg: 'CCC', ' #CCC', 'CCCCCC', '#CCCCCC', 'Red', 'green'
     *
     * @throws Exception
     */
    public static function hex2Rgb(string $hex = ''): array
    {
        $regs = null;
        // is the hexColor a color name in the table
        if (!$hex[0] == '#' && array_key_exists($hex, self::$colorChart)) {
            $hex = self::$colorChart[$hex];
        }
        // Convert to a standard 6 char color hex
        if (preg_match('/^(\#)?([A-F0-9]{3})$/i', $hex, $regs)) {   // is 3 char hex
            $hex = $regs[2][0] . $regs[2][0] . $regs[2][1] . $regs[2][1] . $regs[2][2] . $regs[2][2];
        }
        if (!preg_match('/^(\#)?([A-F0-9]{6})$/i', $hex, $regs)) {   // if not a 6 char HEX string
            throw new Exception('Invalid Hex color.');
        }
        $hex = $regs[2];
        $hex = strtoupper($hex);
        $r = [
            'red' => intval(substr($hex, 0, 2), 16),
            'green' => intval(substr($hex, 2, 2), 16),
            'blue' => intval(substr($hex, 4, 2), 16)
        ];
        return $r;
    }

    /**
     * hsl2Rgb
     */
    public static function hsl2Rgb(float $hue = 0.0, float $saturation = 0.0, float $luminosity = 0.0): array
    {
        $h = $hue;
        $s = $saturation;
        $l = $luminosity;
        $r = 0;
        $g = 0;
        $b = 0;
        $c = ( 1 - abs( 2 * $l - 1 ) ) * $s;
        $x = $c * ( 1 - abs( fmod( ( $h / 60 ), 2 ) - 1 ) );
        $m = $l - ( $c / 2 );
        if ( $h < 60 ) {
            $r = $c;
            $g = $x;
            $b = 0;
        } else if ( $h < 120 ) {
            $r = $x;
            $g = $c;
            $b = 0;
        } else if ( $h < 180 ) {
            $r = 0;
            $g = $c;
            $b = $x;
        } else if ( $h < 240 ) {
            $r = 0;
            $g = $x;
            $b = $c;
        } else if ( $h < 300 ) {
            $r = $x;
            $g = 0;
            $b = $c;
        } else {
            $r = $c;
            $g = 0;
            $b = $x;
        }
        $r = ( $r + $m ) * 255;
        $g = ( $g + $m ) * 255;
        $b = ( $b + $m  ) * 255;
        $r = floor( $r );
        $g = floor( $g );
        $b = floor( $b );

        return [
            'red' => $r,
            'green' => $g,
            'blue' => $b
        ];

    }

    /**
     * cymk2Rgb
     * @see https://github.com/spatie/color/blob/main/src/Convert.php
     */
    static function cymk2Rgb(float $cyan = 0, float $yellow = 0, float $magenta = 0, float $key = 0): array
    {
        return [
           'red'   => (int) (255 * (1 - $cyan) * (1 - $key)),
           'green' => (int) (255 * (1 - $magenta) * (1 - $key)),
           'blue'  => (int) (255 * (1 - $yellow) * (1 - $key)),
        ];
    }

    /**
     * Return a value of this color object as a HEX value
     */
    public function toString(bool $hash = false): string
    {
        if ($hash)  return '#' . $this->getHex();
        return $this->getHex();
    }

    /**
     * Return a value of this color object as a HEX value '000000' - 'FFFFFF'
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Returns colored string for use in CLI scripts
     */
    public static function getCliString(string $string, string $foregroundColor = null, string $backgroundColor = null): string
    {
        $cString = '';
        if (isset(self::$cliFgColorChart[$foregroundColor])) {  // Check if given foreground color found
            $cString .= "\033[" . self::$cliFgColorChart[$foregroundColor] . "m";
        }
        if (isset(self::$cliBgColorChart[$backgroundColor])) {  // Check if given background color found
            $cString .= "\033[" . self::$cliBgColorChart[$backgroundColor] . "m";
        }
        // Add string and end coloring
        $cString .= $string . "\033[0m";
        return $cString;
    }

    public static array $cliFgColorChart = [
        'Black' => '0;30',
        'DarkGray' => '1;30',
        'Blue' => '0;34',
        'Green' => '1;34',
        'LightGreen' => '0;32',
        'Cyan' => '1;32',
        'LightCyan' => '0;36',
        'Red' => '0;31',
        'LightRed' => '1;31',
        'Purple' => '0;35',
        'LightPurple' => '1;35',
        'Brown' => '0;33',
        'Yellow' => '1;33',
        'LightGray' => '0;37',
        'White' => '1;37'
    ];

    public static array $cliBgColorChart = [
        'Black' => '40',
        'Red' => '41',
        'Green' => '42',
        'Yellow' => '43',
        'Blue' => '44',
        'Magenta' => '45',
        'Cyan' => '46',
        'LightGray' => '47'
    ];

    /**
     * A complete list of the color names supported by all major browsers.
     */
    public static array $colorChart = [
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
        'YellowGreen' => '9ACD32'];

}