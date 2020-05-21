<?php
namespace Tk;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Image
{
    /**
     *
     */
    const URI_PIXEL_TRANSPARENT = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

    const URI_PIXEL_WHITE = 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACwAAAAAAQABAAACAkQBADs=';

    const URI_PIXEL_BLACK = 'data:image/gif;base64,R0lGODlhAQABAIAAAAUEBAAAACwAAAAAAQABAAACAkQBADs=';



    /**
     * @var string
     */
    private $currentMem = '16M';

    /**
     * @var null|resource
     */
    private $image = null;
    
    /**
     * @var string
     */
    private $filename = '';
    
    /**
     * @var array
     */
    private $originalInfo = array();

    /**
     * @var int
     */
    private $width = 0;

    /**
     * @var int
     */
    private $height = 0;


    /**
     * constructor
     *
     * @param string $filename
     * @throws \Tk\Exception
     */
    public function __construct($filename = null)
    {
        if ($filename)
            $this->load($filename);
    }

    /**
     * destructor
     */
    public function __destruct()
    {
        if ($this->image) {
            imagedestroy($this->image);
        }
        $this->memReset();
    }

    /**
     * @param string $filename
     * @return Image
     * @throws \Tk\Exception
     */
    static function create($filename)
    {
        return new static($filename);
    }

    /**
     * @param int $width
     * @param int $height
     * @param null|\Tk\Color $bgcolour
     * @return Image
     * @throws Exception
     */
    static function createBlankPng($width = 256, $height = 256, $bgcolour = null)
    {
        $obj = new static();
        $obj->image = imagecreatetruecolor($width, $height);
        imagealphablending($obj->image, false);
        imagesavealpha($obj->image, true);
        $transparent = imagecolorallocatealpha($obj->image, 0, 0, 0, 127);
        imagefill($obj->image, 0, 0, $transparent);
        if ($bgcolour) {
            $c = imagecolorallocate($obj->image, $bgcolour->getRed(), $bgcolour->getGreen(), $bgcolour->getBlue());
            imagefill($obj->image, 0, 0, $c);
        }

        $obj->originalInfo = array(
            'width' => $width,
            'height' => $height,
            'orientation' => $obj->getOrientation(),
            'format' => 'png',
            'mime' => 'image/png'
        );
        return $obj;
    }

    /**
     * Load an image
     *
     * @param string $filename the image to be loaded (required)
     * @return $this
     * @throws Exception
     */
    public function load($filename)
    {
        // Require GD library
        if (!extension_loaded('gd')) {
            throw new Exception('Required extension GD is not loaded.');
        }
        if (!is_file($filename) && !filter_var($filename, \FILTER_VALIDATE_URL)) {
            throw new Exception('File Not Found: ' . $filename);
        }
        $this->filename = $filename;
        $this->memAlloc();
        $info = getimagesize($this->filename);
        switch ($info['mime']) {
            case 'image/gif':
                $this->image = imagecreatefromgif($this->filename);
                break;
            case 'image/jpeg':
                $this->image = imagecreatefromjpeg($this->filename);
                break;
            case 'image/png':
                $this->image = imagecreatefrompng($this->filename);
//                imagealphablending($this->image, false);
                imagesavealpha($this->image, true);
                break;
            default:
                throw new Exception('Invalid image: ' . $this->filename);
                break;
        }

        $this->originalInfo = array(
            'width' => $info[0],
            'height' => $info[1],
            'orientation' => $this->getOrientation(),
            'format' => preg_replace('/^image\//', '', $info['mime']),
            'mime' => $info['mime']
        );

        $this->width = $info[0];
        $this->height = $info[1];

        return $this;
    }

    /**
     * Save an image
     *    Notes:
     *        The resulting format will be determined by the file extension.
     *
     * @param string $filename  the filename to save to (defaults to original file)
     * @param int $quality 0-9 for PNG, 0-100 for JPEG
     * @return $this
     * @throws Exception
     */
    public function save($filename = null, $quality = null)
    {
        if (!$filename)
            $filename = $this->filename;
        // Determine format via file extension (fall back to original format)
        $format = 'png';
        if ($this->fileExt($filename)) {
            $format = $this->fileExt($filename);
        } else if (!empty($this->originalInfo['format'])) {
            $format = $this->originalInfo['format'];
        }

        // Determine output format
        switch (strtolower($format)) {
            case 'gif':
                $result = imagegif($this->image, $filename);
                break;
            case 'jpg':
            case 'jpeg':
                if ($quality === null)
                    $quality = 85;
                $quality = $this->keepWithin($quality, 0, 100);
                $result = imagejpeg($this->image, $filename, $quality);
                break;
            case 'png':
                if ($quality === null)
                    $quality = 9;
                $quality = $this->keepWithin($quality, 0, 9);
                $result = imagepng($this->image, $filename, $quality);
                break;
            default:
                throw new Exception('Unsupported format');
        }

        if (!$result)
            throw new Exception('Unable to save image: ' . $filename);

        return $this;
    }

    /**
     * Stream the image to the output buffer
     *
     * @param int $quality
     * @return $this
     */
    public function stream($quality = null)
    {
        $format = 'png';
        if ($this->fileExt($this->filename)) {
            $format = $this->fileExt($this->filename);
        } else if (!empty($this->originalInfo['format'])) {
            $format = $this->originalInfo['format'];
        }

        switch (strtolower($format)) {
            case 'jpeg' :
            case 'jpg' :
                if ($quality === null)
                    $quality = 85;
                $quality = $this->keepWithin($quality, 0, 100);
                imagejpeg($this->image, null, $quality);
                header('Content-Type: image/jpeg');
                break;
            case 'gif' :
                header('Content-Type: image/gif');
                imagegif($this->image);
                break;
            case 'png' :
                header('Content-Type: image/png');
                if ($quality === null)
                    $quality = 9;
                $quality = $this->keepWithin($quality, 0, 9);
                imagepng($this->image, null, $quality);
                break;
        }
        return $this;
    }

    /**
     * Get info about the original image
     *
     *    Returns
     *
     *    array(
     *        width => 320,
     *        height => 200,
     *        orientation => ['portrait', 'landscape', 'square'],
     *        exif => array(...),
     *        mime => ['image/jpeg', 'image/gif', 'image/png'],
     *        format => ['jpeg', 'gif', 'png']
     *    )
     * @return array
     */
    public function getOriginalInfo()
    {
        return $this->originalInfo;
    }

    /**
     * @return null|resource
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Get the current width
     * @return int
     */
    public function getWidth()
    {
        return imagesx($this->image);
    }

    /**
     * Get the current height
     * @return int
     */
    public function getHeight()
    {
        return imagesy($this->image);
    }

    /**
     * Get the current orientation ('portrait', 'landscape', or 'square')
     * @return string
     */
    public function getOrientation()
    {
        if (imagesx($this->image) > imagesy($this->image))
            return 'landscape';
        if (imagesx($this->image) < imagesy($this->image))
            return 'portrait';
        return 'square';
    }

    /**
     * Flip an image horizontally or vertically
     *
     * @param string $direction 'x' or 'y'
     * @return $this
     */
    public function flip($direction)
    {
        $new = imagecreatetruecolor($this->width, $this->height);
        imagealphablending($new, false);
        imagesavealpha($new, true);
        switch (strtolower($direction)) {
            case 'y':
                for ($y = 0; $y < $this->height; $y++)
                    imagecopy($new, $this->image, 0, $y, 0, $this->height - $y - 1, $this->width, 1);
                break;
            default:
                for ($x = 0; $x < $this->width; $x++)
                    imagecopy($new, $this->image, $x, 0, $this->width - $x - 1, 0, 1, $this->height);
                break;
        }
        $this->image = $new;
        return $this;
    }

    /**
     * Rotate an image
     *
     * @param int $angle 0 - 360 (required)
     * @param string $bg_color hex color for the background
     * @return $this
     */
    public function rotate($angle, $bg_color = '#000000')
    {
        $rgb = $this->hex2rgb($bg_color);
        $bg_color = imagecolorallocate($this->image, $rgb['r'], $rgb['g'], $rgb['b']);
        $new = imagerotate($this->image, $this->keepWithin($angle, -360, 360), $bg_color);
        $this->width = imagesx($new);
        $this->height = imagesy($new);
        $this->image = $new;

        return $this;
    }


    /**
     * @param $filename
     * @return array
     */
    private function getExif($filename)
    {
        if (function_exists('exif_read_data')) {
            try {
                return @exif_read_data($filename);
            } catch(\Exception $e) {}
        }
        // see http://php.net/manual/en/function.exif-read-data.php
        if (preg_match('@\x12\x01\x03\x00\x01\x00\x00\x00(.)\x00\x00\x00@', file_get_contents($filename), $matches)) {
            return array('Orientation' => ord($matches[1]));
        }
        return array();
    }

    /**
     * Rotates and/or flips an image automatically so the orientation will
     * be correct (based on exif 'Orientation')
     */
    public function autoOrient()
    {
        $angle = 0;
        $flip = false;
        
        $exif = $this->getExif($this->filename);
        $orientation = 0;
        if (!empty($exif['Orientation'])) $orientation = $exif['Orientation'];
        
        // Adjust orientation
        switch ($orientation) {
            case 2:
                $angle = 0;
                $flip = true;
                break;
            case 3:
                $angle = 180;
                $flip = false;
                break;
            case 4:
                $angle = 180;
                $flip = true;
                break;
            case 5:
                $angle = 90;
                $flip = true;
                break;
            case 6:
                $angle = 90;
                $flip = false;
                break;
            case 7:
                $angle = 270;
                $flip = true;
                break;
            case 8:
                $angle = 270;
                $flip = false;
                break;
            default:
                $angle = 0;
        }
        
        if ($angle > 0)
            $this->rotate($angle);
        if ($flip)
            $this->flip('x');
        return $this;
    }

    /**
     * Resize an image to the specified dimensions
     *
     * @param int $width the width of the resulting image
     * @param int $height the height of the resulting image
     * @return $this
     */
    public function resize($width, $height)
    {

        $new = imagecreatetruecolor($width, $height);
        imagealphablending($new, false);
        imagesavealpha($new, true);
        imagecopyresampled($new, $this->image, 0, 0, 0, 0, $width, $height, $this->width, $this->height);

        $this->width = $width;
        $this->height = $height;
        $this->image = $new;

        return $this;
    }

    /**
     * Fit to width (proportionally resize to specified width)
     * @param int $width the width of the resulting image
     * @return $this
     */
    public function fitToWidth($width)
    {
        $aspect_ratio = $this->height / $this->width;
        $height = $width * $aspect_ratio;
        return $this->resize($width, $height);
    }

    /**
     * Fit to height (proportionally resize to specified height)
     * @param int $height the height of the resulting image
     * @return $this
     */
    public function fitToHeight($height)
    {
        $aspect_ratio = $this->height / $this->width;
        $width = $height / $aspect_ratio;
        return $this->resize($width, $height);
    }

    /**
     * Best fit (proportionally resize to fit in specified width/height)
     * @param int $max_width
     * @param int $max_height
     * @return Image
     */
    public function bestFit($max_width, $max_height)
    {
        // If it already fits, there's nothing to do
        if ($this->width <= $max_width && $this->height <= $max_height)
            return $this;

        // Determine aspect ratio
        $aspect_ratio = $this->height / $this->width;

        // Make width fit into new dimensions
        if ($this->width > $max_width) {
            $width = $max_width;
            $height = $width * $aspect_ratio;
        } else {
            $width = $this->width;
            $height = $this->height;
        }

        // Make height fit into new dimensions
        if ($height > $max_height) {
            $height = $max_height;
            $width = $height / $aspect_ratio;
        }

        return $this->resize($width, $height);
    }

    /**
     * Crop an image
     *
     * @param int $x1 left
     * @param int $y1 top
     * @param int $x2 right
     * @param int $y2 bottom
     * @return $this
     */
    public function crop($x1, $y1, $x2, $y2)
    {
        // Determine crop size
        if ($x2 < $x1)
            list($x1, $x2) = array($x2, $x1);
        if ($y2 < $y1)
            list($y1, $y2) = array($y2, $y1);
        $crop_width = $x2 - $x1;
        $crop_height = $y2 - $y1;

        $new = imagecreatetruecolor($crop_width, $crop_height);
        imagealphablending($new, false);
        imagesavealpha($new, true);
        imagecopyresampled($new, $this->image, 0, 0, $x1, $y1, $crop_width, $crop_height, $crop_width, $crop_height);

        $this->width = $crop_width;
        $this->height = $crop_height;
        $this->image = $new;

        return $this;
    }

    /**
     * Square crop (great for thumbnails)
     *
     * @param int $size the size in pixels of the resulting image (width and height are the same) (optional)
     * @return $this
     */
    public function squareCrop($size = null)
    {
        // Calculate measurements
        if ($this->width > $this->height) {
            // Landscape
            $x_offset = ($this->width - $this->height) / 2;
            $y_offset = 0;
            $square_size = $this->width - ($x_offset * 2);
        } else {
            // Portrait
            $x_offset = 0;
            $y_offset = ($this->height - $this->width) / 2;
            $square_size = $this->height - ($y_offset * 2);
        }

        // Trim to square
        $this->crop($x_offset, $y_offset, $x_offset + $square_size, $y_offset + $square_size);

        // Resize
        if ($size)
            $this->resize($size, $size);

        return $this;
    }

    /**
     * Desaturate (grayscale)
     */
    public function desaturate()
    {
        imagefilter($this->image, \IMG_FILTER_GRAYSCALE);
        return $this;
    }

    /**
     * Invert
     */
    public function invert()
    {
        imagefilter($this->image, \IMG_FILTER_NEGATE);
        return $this;
    }

    /**
     * Brightness
     *
     * @param int $level min = -100, max, 100 (required)
     * @return $this
     */
    public function brightness($level)
    {
        imagefilter($this->image, \IMG_FILTER_BRIGHTNESS, $this->keepWithin($level, -255, 255));
        return $this;
    }

    /**
     * Contrast
     *
     * @param int $level min = -100, max, 100 (required)
     * @return $this
     */
    public function contrast($level)
    {
        imagefilter($this->image, \IMG_FILTER_CONTRAST, $this->keepWithin($level, -100, 100));
        return $this;
    }

    /**
     * Colorize (requires PHP 5.2.5+)
     *
     * @param string $color    any valid hex color (required)
     * @param float $opacity  0 - 1 (required)
     * @return $this
     */
    public function colorize($color, $opacity)
    {
        $rgb = $this->hex2rgb($color);
        $alpha = $this->keepWithin(127 - (127 * $opacity), 0, 127);
        imagefilter($this->image, \IMG_FILTER_COLORIZE, $this->keepWithin($rgb['r'], 0, 255), $this->keepWithin($rgb['g'], 0, 255), $this->keepWithin($rgb['b'], 0, 255), $alpha);
        return $this;
    }

    /**
     * Edge Detect
     * @return $this
     */
    public function edges()
    {
        imagefilter($this->image, \IMG_FILTER_EDGEDETECT);
        return $this;
    }

    /**
     * Emboss
     * @return $this
     */
    public function emboss()
    {
        imagefilter($this->image, \IMG_FILTER_EMBOSS);
        return $this;
    }

    /**
     * Mean Remove
     * @return $this
     */
    public function meanRemove()
    {
        imagefilter($this->image, \IMG_FILTER_MEAN_REMOVAL);
        return $this;
    }

    /**
     * Blur
     *
     * @param string $type  'selective' or 'gaussian' (default = selective)
     * @param int $passes   the number of times to apply the filter
     * @return $this
     */
    public function blur($type = 'selective', $passes = 1)
    {
        switch (strtolower($type)) {
            case 'gaussian':
                $type = \IMG_FILTER_GAUSSIAN_BLUR;
                break;
            default:
                $type = \IMG_FILTER_SELECTIVE_BLUR;
                break;
        }

        for ($i = 0; $i < $passes; $i++)
            imagefilter($this->image, $type);

        return $this;
    }

    /**
     * Sketch
     * @return $this
     */
    public function sketch()
    {
        imagefilter($this->image, \IMG_FILTER_MEAN_REMOVAL);
        return $this;
    }

    /**
     * Smooth
     *
     * @param int $level  min = -10, max = 10
     * @return $this
     */
    public function smooth($level)
    {
        imagefilter($this->image, \IMG_FILTER_SMOOTH, $this->keepWithin($level, -10, 10));
        return $this;
    }

    /**
     * Pixelate (requires PHP 5.3+)
     *
     * @param int $block_size   the size in pixels of each resulting block (default = 10)
     * @return $this
     */
    public function pixelate($block_size = 10)
    {
        imagefilter($this->image, \IMG_FILTER_PIXELATE, $block_size, true);
        return $this;
    }

    /**
     * Sepia
     * @return $this
     */
    public function sepia()
    {
        imagefilter($this->image, \IMG_FILTER_GRAYSCALE);
        imagefilter($this->image, \IMG_FILTER_COLORIZE, 100, 50, 0);
        return $this;
    }

    /**
     * Overlay (overlay an image on top of another; works with 24-big PNG alpha-transparency)
     *
     * @param string $overlay_file the image to use as a overlay (required)
     * @param string $position 'center', 'top', 'left', 'bottom', 'right', 'top left',
     *                'top right', 'bottom left', 'bottom right'
     * @param int $opacity overlay opacity (0 - 1)
     * @param int $x_offset horizontal offset in pixels
     * @param int $y_offset vertical offset in pixels
     * @return $this
     * @throws \Tk\Exception
     */
    public function overlay($overlay_file, $position = 'center', $opacity = 1, $x_offset = 0, $y_offset = 0)
    {
        // Load overlay image
        $overlay = new self($overlay_file);
        // Convert opacity
        $opacity = $opacity * 100;
        // Determine position
        switch (strtolower($position)) {

            case 'top left':
                $x = 0 + $x_offset;
                $y = 0 + $y_offset;
                break;
            case 'top right':
                $x = $this->width - $overlay->width + $x_offset;
                $y = 0 + $y_offset;
                break;
            case 'top':
                $x = ($this->width / 2) - ($overlay->width / 2) + $x_offset;
                $y = 0 + $y_offset;
                break;
            case 'bottom left':
                $x = 0 + $x_offset;
                $y = $this->height - $overlay->height + $y_offset;
                break;
            case 'bottom right':
                $x = $this->width - $overlay->width + $x_offset;
                $y = $this->height - $overlay->height + $y_offset;
                break;
            case 'bottom':
                $x = ($this->width / 2) - ($overlay->width / 2) + $x_offset;
                $y = $this->height - $overlay->height + $y_offset;
                break;
            case 'left':
                $x = 0 + $x_offset;
                $y = ($this->height / 2) - ($overlay->height / 2) + $y_offset;
                break;
            case 'right':
                $x = $this->width - $overlay->width + $x_offset;
                $y = ($this->height / 2) - ($overlay->height / 2) + $y_offset;
                break;
            case 'center':
            default:
                $x = ($this->width / 2) - ($overlay->width / 2) + $x_offset;
                $y = ($this->height / 2) - ($overlay->height / 2) + $y_offset;
                break;
        }
        $this->imagecopymergeAlpha($this->image, $overlay->image, $x, $y, 0, 0, $overlay->width, $overlay->height, $opacity);
        return $this;
    }

    /**
     * Text (adds text to an image)
     *
     * @param string $text the text to add (required)
     * @param string $font_file the font to use (required)
     * @param string $font_size font size in points
     * @param string $color font color in hex
     * @param string $position 'center', 'top', 'left', 'bottom', 'right', 'top left',
     *                         'top right', 'bottom left', 'bottom right'
     * @param int $x_offset horizontal offset in pixels
     * @param int $y_offset vertical offset in pixels
     * @return $this
     * @throws Exception
     */
    public function text($text, $font_file, $font_size = '12', $color = '#000000', $position = 'center', $x_offset = 0, $y_offset = 0)
    {
        // todo - this method could be improved to support the text angle
        $angle = 0;
        $rgb = $this->hex2rgb($color);
        $color = imagecolorallocate($this->image, $rgb['r'], $rgb['g'], $rgb['b']);

        // Determine textbox size
        $box = imagettfbbox($font_size, $angle, $font_file, $text);
        if (!$box)
            throw new Exception('Unable to load font: ' . $font_file);
        $box_width = abs($box[6] - $box[2]);
        $box_height = abs($box[7] - $box[1]);

        // Determine position
        switch (strtolower($position)) {

            case 'top left':
                $x = 0 + $x_offset;
                $y = 0 + $y_offset + $box_height;
                break;
            case 'top right':
                $x = $this->width - $box_width + $x_offset;
                $y = 0 + $y_offset + $box_height;
                break;
            case 'top':
                $x = ($this->width / 2) - ($box_width / 2) + $x_offset;
                $y = 0 + $y_offset + $box_height;
                break;
            case 'bottom left':
                $x = 0 + $x_offset;
                $y = $this->height - $box_height + $y_offset + $box_height;
                break;
            case 'bottom right':
                $x = $this->width - $box_width + $x_offset;
                $y = $this->height - $box_height + $y_offset + $box_height;
                break;
            case 'bottom':
                $x = ($this->width / 2) - ($box_width / 2) + $x_offset;
                $y = $this->height - $box_height + $y_offset + $box_height;
                break;
            case 'left':
                $x = 0 + $x_offset;
                $y = ($this->height / 2) - (($box_height / 2) - $box_height) + $y_offset;
                break;
            case 'right';
                $x = $this->width - $box_width + $x_offset;
                $y = ($this->height / 2) - (($box_height / 2) - $box_height) + $y_offset;
                break;
            case 'center':
            default:
                $x = ($this->width / 2) - ($box_width / 2) + $x_offset;
                $y = ($this->height / 2) - (($box_height / 2) - $box_height) + $y_offset;
                break;
        }

        imagettftext($this->image, $font_size, $angle, $x, $y, $color, $font_file, $text);

        return $this;
    }


    /**
     * This function will overlay four tiles on top of this image
     *
     * @param array $imageList and array of up to 4 image paths to time on this image
     * @param int $padding The padding between the images in pixels
     * @throws Exception
     */
    public function makeTileMontage($imageList, $padding = 10)
    {
        $thumbW = ($this->getWidth()-$padding*3)/2;
        $files = array();
        foreach ($imageList as $i => $path) {
            if (is_file($path)) {
                $files[] = $path;
                $img = \Tk\Image::create($path);
                $img->squareCrop($thumbW);
                switch ($i) {
                    case 0:
                        imagecopymerge($this->getImage(), $img->getImage(), $padding, $padding, 0, 0, $img->getWidth(), $img->getHeight(), 100);
                        break;
                    case 1:
                        imagecopymerge($this->getImage(), $img->getImage(), $padding, $padding+$thumbW+$padding, 0, 0, $img->getWidth(), $img->getHeight(), 100);
                        break;
                    case 2:
                        imagecopymerge($this->getImage(), $img->getImage(), $padding+$thumbW+$padding, $padding, 0, 0, $img->getWidth(), $img->getHeight(), 100);
                        break;
                    case 3:
                        imagecopymerge($this->getImage(), $img->getImage(), $padding+$thumbW+$padding, $padding+$thumbW+$padding, 0, 0, $img->getWidth(), $img->getHeight(), 100);
                        break;
                }

            }
        }
    }





    /**
     * Use this to set the memory allocation for image resizing
     *
     */
    private function memAlloc()
    {
        $this->currentMem = ini_get('memory_limit');
        $imageInfo = getimagesize($this->filename);
        if (!isset($imageInfo['bits']) || !isset($imageInfo['channels'])) {
            if (ini_set( 'memory_limit', '128M' ) === false) {
                return false;
            }
            return true;
        }
        $MB = 1048576;  // number of bytes in 1M
        $K64 = 65536;    // number of bytes in 64K
        $TWEAKFACTOR = 3.5;  // Or whatever works for you
        $memoryNeeded = round( ( $imageInfo[0] * $imageInfo[1]
                                               * $imageInfo['bits']
                                               * $imageInfo['channels'] / 8
                                 + $K64
                               ) * $TWEAKFACTOR
                             );

        $memoryLimit = intval(ini_get('memory_limit')) * $MB;
        if (function_exists('memory_get_usage') && memory_get_usage() + $memoryNeeded > $memoryLimit) {
            $newLimit = $memoryLimit + ceil((memory_get_usage() + $memoryNeeded - $memoryLimit) / $MB);
            if (ini_set( 'memory_limit', $newLimit . 'M' ) === false) {
                return false;
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Reset the memory allocation back to the default value
     *
     */
    private function memReset()
    {
        ini_set('memory_limit', $this->currentMem);
    }

    /**
     * Same as PHP's imagecopymerge() function, except preserves alpha-transparency in 24-bit PNGs
     * @param resource $dst_im
     * @param resource $src_im
     * @param int $dst_x
     * @param int $dst_y
     * @param int $src_x
     * @param int $src_y
     * @param int $src_w
     * @param int $src_h
     * @param int $pct The two images will be merged according to pct
     *      which can range from 0 to 100. When pct = 0,
     *      no action is taken, when 100 this function behaves identically
     *      to imagecopy for pallete images, while it
     *      implements alpha transparency for true colour images.
     *
     * @return bool
     */
    private function imagecopymergeAlpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct)
    {
        $cut = imagecreatetruecolor($src_w, $src_h);
        imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);
        imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);
        return imagecopymerge($dst_im, $cut, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct);
    }

    /**
     * Ensures $value is always within $min and $max range.
     * If lower, $min is returned. If higher, $max is returned.
     * @param int $value
     * @param int $min
     * @param int $max
     * @return int
     */
    private function keepWithin($value, $min, $max)
    {
        if ($value < $min)
            return $min;
        if ($value > $max)
            return $max;
        return $value;
    }

    /**
     * Returns the file extension of the specified file
     * @param string $filename
     * @return null|string
     */
    private function fileExt($filename)
    {

        if (!preg_match('/\./', $filename))
            return '';

        return preg_replace('/^.*\./', '', $filename);
    }

    /**
     * Converts a hex color value to its RGB equivalent
     * NOTE: This functionality also exists in the Tk_Color object
     *
     * @param string $hex_color
     * @return array|bool
     */
    private function hex2rgb($hex_color)
    {

        if ($hex_color[0] == '#')
            $hex_color = substr($hex_color, 1);
        if (strlen($hex_color) == 6) {
            list($r, $g, $b) = array(
                $hex_color[0] . $hex_color[1],
                $hex_color[2] . $hex_color[3],
                $hex_color[4] . $hex_color[5]
            );
        } elseif (strlen($hex_color) == 3) {
            list($r, $g, $b) = array(
                $hex_color[0] . $hex_color[0],
                $hex_color[1] . $hex_color[1],
                $hex_color[2] . $hex_color[2]
            );
        } else {
            return false;
        }

        return array(
            'r' => hexdec($r),
            'g' => hexdec($g),
            'b' => hexdec($b)
        );
    }

}

