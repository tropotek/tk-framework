<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk;

/**
 * Identicon
 * This class generates persistent specific geometric icons for each user based on the ideas of
 * <a href="http://www.docuverse.com/blog/donpark/2007/01/18/visual-security-9-block-ip-identification">Don Park</a>.
 *
 * Usage:
 * <code>
 * <?php
 *   $idenicon = Identicon::create()->makeIcon($seed, $size); // Output to buffer as image....
 * 
 *   $idenicon = Identicon::create()->makeIcon($seed, $size, $filename); // Output to $filename as image....
 * ?>
 * </code>
 * 
 * options:
 *     $options = array(
 *         'size' => 128,
 *         'backr' => array(255, 255),
 *         'backg' => array(255, 255),
 *         'backb' => array(255, 255),
 *         'forer' => array(1, 255),
 *         'foreg' => array(1, 255),
 *         'foreb' => array(1, 255)
 *     );
 * 
 * 
 * 
 * @link http://scott.sherrillmix.com/blog/blogger/wp_identicon/
 * @package Tk
 */
class Identicon
{

    protected $options;
    protected $blocks;
    protected $shapes;
    protected $rotatable;
    protected $square;
    protected $im;
    protected $colors;
    protected $size;
    protected $blocksize;
    protected $quarter;
    protected $half;
    protected $diagonal;
    protected $halfdiag;
    protected $transparent = false;
    protected $centers;
    protected $shapes_mat;
    protected $symmetric_num;
    protected $rot_mat;
    protected $invert_mat;
    protected $rotations;

    /**
     * __construct
     *
     * @param array $options
     * @param int $blocks
     */
    public function __construct($blocks = 4, $options = null)
    {
        $this->init($blocks, $options);
    }
    
    /**
     * Create an Identicon object
     * 
     * @param int $blocks
     * @param array $options
     * @return \Tk\Identicon
     */
    static function create($blocks = 4, $options = null)
    {
        return new self($blocks, $options);
    }

    /**
     * init
     *
     * @param array $options
     * @param int $blocks
     */
    protected function init($blocks = 4, $options = null)
    {
        if (!$options) {
            $options = array(
                'size' => 128,
                'backr' => array(255, 255),
                'backg' => array(255, 255),
                'backb' => array(255, 255),
                'forer' => array(1, 255),
                'foreg' => array(1, 255),
                'foreb' => array(1, 255)
            );
        }

        $this->blocks = $blocks;
        $this->options = $options;
        $this->blocksize = 80;
        $this->size = $this->blocks * $this->blocksize;
        $this->quarter = $this->blocksize / 4;
        $this->half = $this->blocksize / 2;
        $this->diagonal = sqrt($this->half * $this->half + $this->half * $this->half);
        $this->halfdiag = $this->diagonal / 2;
        $this->shapes = array(
        array(array(array(90, $this->half), array(135, $this->diagonal), array(225, $this->diagonal), array(270, $this->half))), //0 rectangular half block
        array(array(array(45, $this->diagonal), array(135, $this->diagonal), array(225, $this->diagonal), array(315, $this->diagonal))), //1 full block
        array(array(array(45, $this->diagonal), array(135, $this->diagonal), array(225, $this->diagonal))), //2 diagonal half block
        array(array(array(90, $this->half), array(225, $this->diagonal), array(315, $this->diagonal))), //3 triangle
        array(array(array(0, $this->half), array(90, $this->half), array(180, $this->half), array(270, $this->half))), //4 diamond
        array(array(array(0, $this->half), array(135, $this->diagonal), array(270, $this->half), array(315, $this->diagonal))), //5 stretched diamond
        array(array(array(0, $this->quarter), array(90, $this->half), array(180, $this->quarter)), array(array(0, $this->quarter), array(315, $this->diagonal), array(270, $this->half)), array(array(270, $this->half), array(180, $this->quarter), array(225, $this->diagonal))), // 6 triple triangle
        array(array(array(0, $this->half), array(135, $this->diagonal), array(270, $this->half))), //7 pointer
        array(array(array(45, $this->halfdiag), array(135, $this->halfdiag), array(225, $this->halfdiag), array(315, $this->halfdiag))), //9 center square
        array(array(array(180, $this->half), array(225, $this->diagonal), array(0, 0)), array(array(45, $this->diagonal), array(90, $this->half), array(0, 0))), //9 double triangle diagonal
        array(array(array(90, $this->half), array(135, $this->diagonal), array(180, $this->half), array(0, 0))), //10 diagonal square
        array(array(array(0, $this->half), array(180, $this->half), array(270, $this->half))), //11 quarter triangle out
        array(array(array(315, $this->diagonal), array(225, $this->diagonal), array(0, 0))), //12quarter triangle in
        array(array(array(90, $this->half), array(180, $this->half), array(0, 0))), //13 eighth triangle in
        array(array(array(90, $this->half), array(135, $this->diagonal), array(180, $this->half))), //14 eighth triangle out
        array(array(array(90, $this->half), array(135, $this->diagonal), array(180, $this->half), array(0, 0)), array(array(0, $this->half), array(315, $this->diagonal), array(270, $this->half), array(0, 0))), //15 double corner square
        array(array(array(315, $this->diagonal), array(225, $this->diagonal), array(0, 0)), array(array(45, $this->diagonal), array(135, $this->diagonal), array(0, 0))), //16 double quarter triangle in
        array(array(array(90, $this->half), array(135, $this->diagonal), array(225, $this->diagonal))), //17 tall quarter triangle
        array(array(array(90, $this->half), array(135, $this->diagonal), array(225, $this->diagonal)), array(array(45, $this->diagonal), array(90, $this->half), array(270, $this->half))), //18 double tall quarter triangle
        array(array(array(90, $this->half), array(135, $this->diagonal), array(225, $this->diagonal)), array(array(45, $this->diagonal), array(90, $this->half), array(0, 0))), //19 tall quarter + eighth triangles
        array(array(array(135, $this->diagonal), array(270, $this->half), array(315, $this->diagonal))), //20 tipped over tall triangle
        array(array(array(180, $this->half), array(225, $this->diagonal), array(0, 0)), array(array(45, $this->diagonal), array(90, $this->half), array(0, 0)), array(array(0, $this->half), array(0, 0), array(270, $this->half))), //21 triple triangle diagonal
        array(array(array(0, $this->quarter), array(315, $this->diagonal), array(270, $this->half)), array(array(270, $this->half), array(180, $this->quarter), array(225, $this->diagonal))), //22 double triangle flat
        array(array(array(0, $this->quarter), array(45, $this->diagonal), array(315, $this->diagonal)), array(array(180, $this->quarter), array(135, $this->diagonal), array(225, $this->diagonal))), //23 opposite 8th triangles
        array(array(array(0, $this->quarter), array(45, $this->diagonal), array(315, $this->diagonal)), array(array(180, $this->quarter), array(135, $this->diagonal), array(225, $this->diagonal)), array(array(180, $this->quarter), array(90, $this->half), array(0, $this->quarter), array(270, $this->half))), //24 opposite 8th triangles + diamond
        array(array(array(0, $this->quarter), array(90, $this->quarter), array(180, $this->quarter), array(270, $this->quarter))), //25 small diamond
        array(array(array(0, $this->quarter), array(45, $this->diagonal), array(315, $this->diagonal)), array(array(180, $this->quarter), array(135, $this->diagonal), array(225, $this->diagonal)), array(array(270, $this->quarter), array(225, $this->diagonal), array(315, $this->diagonal)), array(array(90, $this->quarter), array(135, $this->diagonal), array(45, $this->diagonal))), //26 4 opposite 8th triangles
        array(array(array(315, $this->diagonal), array(225, $this->diagonal), array(0, 0)), array(array(0, $this->half), array(90, $this->half), array(180, $this->half))), //27 double quarter triangle parallel
        array(array(array(135, $this->diagonal), array(270, $this->half), array(315, $this->diagonal)), array(array(225, $this->diagonal), array(90, $this->half), array(45, $this->diagonal))), //28 double overlapping tipped over tall triangle
        array(array(array(90, $this->half), array(135, $this->diagonal), array(225, $this->diagonal)), array(array(315, $this->diagonal), array(45, $this->diagonal), array(270, $this->half))), //29 opposite double tall quarter triangle
        array(array(array(0, $this->quarter), array(45, $this->diagonal), array(315, $this->diagonal)), array(array(180, $this->quarter), array(135, $this->diagonal), array(225, $this->diagonal)), array(array(270, $this->quarter), array(225, $this->diagonal), array(315, $this->diagonal)), array(array(90, $this->quarter), array(135, $this->diagonal), array(45, $this->diagonal)), array(array(0, $this->quarter), array(90, $this->quarter), array(180, $this->quarter), array(270, $this->quarter))), //30 4 opposite 8th triangles+tiny diamond
        array(array(array(0, $this->half), array(90, $this->half), array(180, $this->half), array(270, $this->half), array(270, $this->quarter), array(180, $this->quarter), array(90, $this->quarter), array(0, $this->quarter))), //31 diamond C
        array(array(array(0, $this->quarter), array(90, $this->half), array(180, $this->quarter), array(270, $this->half))), //32 narrow diamond
        array(array(array(180, $this->half), array(225, $this->diagonal), array(0, 0)), array(array(45, $this->diagonal), array(90, $this->half), array(0, 0)), array(array(0, $this->half), array(0, 0), array(270, $this->half)), array(array(90, $this->half), array(135, $this->diagonal), array(180, $this->half))), //33 quadruple triangle diagonal
        array(array(array(0, $this->half), array(90, $this->half), array(180, $this->half), array(270, $this->half), array(0, $this->half), array(0, $this->quarter), array(270, $this->quarter), array(180, $this->quarter), array(90, $this->quarter), array(0, $this->quarter))), //34 diamond donut
        array(array(array(90, $this->half), array(45, $this->diagonal), array(0, $this->quarter)), array(array(0, $this->half), array(315, $this->diagonal), array(270, $this->quarter)), array(array(270, $this->half), array(225, $this->diagonal), array(180, $this->quarter))), //35 triple turning triangle
        array(array(array(90, $this->half), array(45, $this->diagonal), array(0, $this->quarter)), array(array(0, $this->half), array(315, $this->diagonal), array(270, $this->quarter))), //36 double turning triangle
        array(array(array(90, $this->half), array(45, $this->diagonal), array(0, $this->quarter)), array(array(270, $this->half), array(225, $this->diagonal), array(180, $this->quarter))), //37 diagonal opposite inward double triangle
        array(array(array(90, $this->half), array(225, $this->diagonal), array(0, 0), array(315, $this->diagonal))), //38 star fleet
        array(array(array(90, $this->half), array(225, $this->diagonal), array(0, 0), array(315, $this->halfdiag), array(225, $this->halfdiag), array(225, $this->diagonal), array(315, $this->diagonal))), //39 hollow half triangle
        array(array(array(90, $this->half), array(135, $this->diagonal), array(180, $this->half)), array(array(270, $this->half), array(315, $this->diagonal), array(0, $this->half))), //40 double eighth triangle out
        array(array(array(90, $this->half), array(135, $this->diagonal), array(180, $this->half), array(180, $this->quarter)), array(array(270, $this->half), array(315, $this->diagonal), array(0, $this->half), array(0, $this->quarter))), //42 double slanted square
        array(array(array(0, $this->half), array(45, $this->halfdiag), array(0, 0), array(315, $this->halfdiag)), array(array(180, $this->half), array(135, $this->halfdiag), array(0, 0), array(225, $this->halfdiag))), //43 double diamond
        array(array(array(0, $this->half), array(45, $this->diagonal), array(0, 0), array(315, $this->halfdiag)), array(array(180, $this->half), array(135, $this->halfdiag), array(0, 0), array(225, $this->diagonal))), //44 double pointer
        );
        $this->rotatable = array(1, 4, 8, 25, 26, 30, 34);
        $this->square = $this->shapes[1][0];
        $this->symmetric_num = ceil($this->blocks * $this->blocks / 4);
        for ($i = 0; $i < $this->blocks; $i++) {
            for ($j = 0; $j < $this->blocks; $j++) {
                $this->centers[$i][$j] = array($this->half + $this->blocksize * $j, $this->half + $this->blocksize * $i);
                $this->shapes_mat[$this->xy2symmetric($i, $j)] = 1;
                $this->rot_mat[$this->xy2symmetric($i, $j)] = 0;
                $this->invert_mat[$this->xy2symmetric($i, $j)] = 0;
                if (floor(($this->blocks - 1) / 2 - $i) >= 0 & floor(($this->blocks - 1) / 2 - $j) >= 0 & ($j >= $i | $this->blocks % 2 == 0)) {
                    $inversei = $this->blocks - 1 - $i;
                    $inversej = $this->blocks - 1 - $j;
                    $symmetrics = array(array($i, $j), array($inversej, $i), array($inversei, $inversej), array($j, $inversei));
                    $fill = array(0, 270, 180, 90);
                    for ($k = 0; $k < count($symmetrics); $k++) {
                        $this->rotations[$symmetrics[$k][0]][$symmetrics[$k][1]] = $fill[$k];
                    }
                }
            }
        }
    }

    /**
     * makeIcon
     *
     * @param string $seed
     * @param type $size
     * @param type $filename
     * @param type $colors
     * @param bool $transparent
     * @return bool|string
     */
    public function makeIcon($seed = '', $size = 0, $filename = null, $transparent = false, $colors = null)
    {
        if (function_exists("gd_info")) {
            if (!$seed) {
                $seed = str_shuffle('1234567890');
            }
            $id = substr(sha1($seed), 0, 10);

            if ($filename && !preg_match('/\.png$/', $filename)) {
                $filename = $filename . '.png';
            }
            if ($size == '') {
                $size = $this->options['size'];
            }


            $this->im = imagecreatetruecolor($this->size, $this->size);
            $this->colors = array(imagecolorallocate($this->im, 255, 255, 255));

            $this->colors = array(imagecolorallocate($this->im, 255, 255, 255), imagecolorallocate($this->im, 0, 0, 0));
            if (!$colors) {
                $this->setRandomness($id);
            }
            $this->transparent = $transparent;

            imagefill($this->im, 0, 0, $this->colors[0]);
            for ($i = 0; $i < $this->blocks; $i++) {
                for ($j = 0; $j < $this->blocks; $j++) {
                    $this->drawShape($i, $j);
                }
            }

            $out = @imagecreatetruecolor($size, $size);
            imagesavealpha($out, true);
            imagealphablending($out, false);
            imagecopyresampled($out, $this->im, 0, 0, 0, 0, $size, $size, $this->size, $this->size);
            imagedestroy($this->im);

            if ($filename) {
                imagepng($out, $filename);
                imagedestroy($out);
            } else {
                header('Content-type: image/png');
                //header('Content-Length: ' . strlen($out));
                imagepng($out);
                imagedestroy($out);
            }

            return $filename;
        } else {
            return false;
        }
    }

    /**
     * xy2symmetric
     *
     * @param type $x
     * @param type $y
     * @return type
     */
    protected function xy2symmetric($x, $y)
    {
        $index = array(floor(abs(($this->blocks - 1) / 2 - $x)), floor(abs(($this->blocks - 1) / 2 - $y)));
        sort($index);
        $index[1]*=ceil($this->blocks / 2);
        $index = array_sum($index);
        return $index;
    }

    /**
     * convert array(array(heading1,distance1),array(heading1,distance1)) to array(x1,y1,x2,y2)
     *
     * @param type $array
     * @param type $centers
     * @param type $rotation
     * @return array
     */
    protected function calcXY($array, $centers, $rotation = 0)
    {
        $output = array();
        $centerx = $centers[0];
        $centery = $centers[1];
        while ($thispoint = array_pop($array)) {
            $y = round($centery + sin(deg2rad($thispoint[0] + $rotation)) * $thispoint[1]);
            $x = round($centerx + cos(deg2rad($thispoint[0] + $rotation)) * $thispoint[1]);
            array_push($output, $x, $y);
        }
        return $output;
    }

    /**
     * draw filled polygon based on an array of (x1,y1,x2,y2,..)
     *
     * @param type $x
     * @param type $y
     */
    protected function drawShape($x, $y)
    {
        $x = (int)$x;
        $y = (int)$y;
        $index = $this->xy2symmetric($x, $y);
        $shape = $this->shapes[$this->shapes_mat[$index]];
        $invert = $this->invert_mat[$index];
        $rotation = $this->rot_mat[$index];
        $centers = $this->centers[$x][$y];
        $invert2 = abs($invert - 1);
        $points = $this->calcXY($this->square, $centers, 0);
        $num = count($points) / 2;
        imagefilledpolygon($this->im, $points, $num, $this->colors[$invert2]);
        foreach ($shape as $subshape) {
            if (!isset($this->rotations[$x][$y]))
                continue;
            $points = $this->calcXY($subshape, $centers, $rotation + $this->rotations[$x][$y]);
            $num = count($points) / 2;
            imagefilledpolygon($this->im, $points, $num, $this->colors[$invert]);
        }
    }

    /**
     * use a seed value to determine shape, rotation, and color
     *
     * @param type $seed
     * @return bool
     */
    protected function setRandomness($seed)
    {
        Math::seed(hexdec($seed));

        foreach ($this->rot_mat as $key => $value) {
            $this->rot_mat[$key] = Math::rand(0, 3) * 90;
            $this->invert_mat[$key] = Math::rand(0, 1);

            if ($key == 0) {
                $this->shapes_mat[$key] = $this->rotatable[$this->arrayRand($this->rotatable)];
            } else {
                $this->shapes_mat[$key] = $this->arrayRand($this->shapes);
            }
        }

        if (array_sum($this->options['backr']) + array_sum($this->options['backg']) + array_sum($this->options['backb']) == 0) {
            $this->colors = array(imagecolorallocatealpha($this->im, 0, 0, 0, 127));
            $this->transparent = true;
            imagealphablending($this->im, false);
            imagesavealpha($this->im, true);
        } else {
            $this->colors = array(imagecolorallocate($this->im, Math::rand($this->options['backr'][0], $this->options['backr'][1]), Math::rand($this->options['backg'][0], $this->options['backg'][1]), Math::rand($this->options['backb'][0], $this->options['backb'][1])));
        }
        $this->colors[1] = imagecolorallocate($this->im, Math::rand($this->options['forer'][0], $this->options['forer'][1]), Math::rand($this->options['foreg'][0], $this->options['foreg'][1]), Math::rand($this->options['foreb'][0], $this->options['foreb'][1]));
        $this->inverts = array(rand(0, 1), rand(0, 1), rand(0, 1));

        // restore random seed
        Math::seed();
        return true;
    }

    /**
     * arrayRand
     * 
     */
    protected function arrayRand(&$items)
    {
        $i = Math::rand(0, count($items) - 1);
        return $i;
    }

}

