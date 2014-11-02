<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk;

/**
 * An abstract representation of file and directory pathnames.
 * This is for the local filesystem only.
 * use Tk_Filesystem for FTP filesystems...
 *
 * @package Tk
 */
class Path extends Object
{

    /**
     * The full path of a file or directory
     * @var string
     */
    private $pathname = '';


    /**
     * Create a \Tk\Path object
     *
     * @param string $spec
     */
    public function __construct($spec)
    {
        $spec = self::ObjToStr($spec);  // \Tk\Object
        if (substr($spec, -1) == '/') {
            $spec = substr($spec, 0, -1);
        }
        if (!self::isAbsolute($spec)) {
            $spec = '/' . $spec;
        }
        $spec = str_replace('//', '/', $spec);
        $this->pathname = $spec;
    }

    /**
     * Create a path object.
     * If the $pathname does not start with a '/' then the site Tk_Path will be prepended
     * to the start of the path
     *
     * @param string $spec
     * @return \Tk\Path
     */
    static function create($spec)
    {
        if ($spec instanceof Tk_Path) {
            return $spec;
        }
        if (!self::isAbsolute($spec) && class_exists('\Tk\Config')) {
            $spec = Config::getInstance()->getSitePath() . '/' . $spec;
        }
        return new self($spec);
    }

    /**
     * Create a url that prepends the data thdoc directory to the spec.
     *
     * @param string $spec
     * @return \Tk\Path
     */
    static function createDataPath($spec)
    {
        if (!self::isAbsolute($spec)) {
            $spec = '/' . $spec;
        }
        if (substr($spec, 0, 6) == '/data/') {
            $spec = substr($spec, 5);
        }
        if (class_exists('\Tk\Config')) {
            $spec = Config::getInstance()->getDataPath() . $spec;
        }
        return new self($spec);
    }

    /**
     * Create a url that prepends the lib directory to the spec.
     *
     * @param string $spec
     * @return \Tk\=====Path
     */
    static function createLibPath($spec)
    {
        if (!self::isAbsolute($spec)) {
            $spec = '/' . $spec;
        }
        if (substr($spec, 0, 5) == '/lib/') {
            $spec = substr($spec, 4);
        }
        if (class_exists('\Tk\Config')) {
            $spec = Config::getInstance()->getLibPath() . $spec;
        }
        return new self($spec);
    }


    /**
     * Returns true if given $path is an absolute path.
     *
     * @param $pathname
     * @return bool true if path is absolute.
     */
    static function isAbsolute($pathname)
    {
        return !empty($pathname) && ($pathname[0] === '/' || preg_match('/^[A-Z]:\\\\/i', $pathname) || substr($pathname, 0, 2) == '\\\\');
    }

    /**
     * Get the bytes from a string like 40M, 10T, 100K
     *
     * @param string $str
     * @return int
     */
    static function string2Bytes($str)
    {
        $sUnit = substr($str, -1);
        $iSize = (int)substr($str, 0, -1);
        switch (strtoupper($sUnit)) {
            case 'Y' :
                $iSize *= 1024; // Yotta
            case 'Z' :
                $iSize *= 1024; // Zetta
            case 'E' :
                $iSize *= 1024; // Exa
            case 'P' :
                $iSize *= 1024; // Peta
            case 'T' :
                $iSize *= 1024; // Tera
            case 'G' :
                $iSize *= 1024; // Giga
            case 'M' :
                $iSize *= 1024; // Mega
            case 'K' :
                $iSize *= 1024; // kilo
        }
        return $iSize;
    }

    /**
     * Convert a value from bytes to a human readable value
     *
     * @param int $bytes
     * @return string
     * @author http://php-pdb.sourceforge.net/samples/viewSource.php?file=twister.php
     */
    static function bytes2String($bytes, $round = 2)
    {
        $tags = array('b', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $index = 0;
        while ($bytes > 999 && isset($tags[$index + 1])) {
            $bytes /= 1024;
            $index++;
        }
        $rounder = 1;
        if ($bytes < 10) {
            $rounder *= 10;
        }
        if ($bytes < 100) {
            $rounder *= 10;
        }
        $bytes *= $rounder;
        settype($bytes, 'integer');
        $bytes /= $rounder;
        if ($round > 0) {
            $bytes = round($bytes, $round);
            return  sprintf('%.'.$round.'f %s', $bytes, $tags[$index]);
        } else {
            return  sprintf('%s %s', $bytes, $tags[$index]);
        }
    }

    /**
     * The trouble is the sum of the byte sizes of the files in your directories
     * is not equal to the amount of disk space consumed, as andudi points out.
     * A 1-byte file occupies 4096 bytes of disk space if the block size is 4096.
     * Couldn't understand why andudi did $s["blksize"]*$s["blocks"]/8.
     * Could only be because $s["blocks"] counts the number of 512-byte disk
     * blocks not the number of $s["blksize"] blocks, so it may as well
     * just be $s["blocks"]*512. Furthermore none of the dirspace suggestions allow
     * for the fact that directories are also files and that they also consume disk
     * space. The following code dskspace addresses all these issues and can also
     * be used to return the disk space consumed by a single non-directory file.
     * It will return much larger numbers than you would have been seeing with
     * any of the other suggestions but I think they are much more realistic
     *
     * @param string $path
     * @return int
     */
    static function diskSpace($path)
    {
        $path = self::ObjToStr($path);
        if (is_dir($path)) {
            $s = stat($path);
        }
        //$space = $s["blocks"] * 512;  // Does not work value $s["blocks"] = -1 allways
        if (!isset($s['size'])) {
            return 0;
        }
        $space = $s["size"];
        if (is_dir($path) && is_readable($path)) {
            $dh = opendir($path);
            while (($file = readdir($dh)) !== false) {
                if ($file != "." and $file != "..") {
                    $space += self::diskSpace($path . "/" . $file);
                }
            }
            closedir($dh);
        }
        return $space;
    }

    /**
     * Returns file extension for this pathname.
     *
     * A the last period ('.') in the pathname is used to delimit the file
     * extension. If the pathname does not have a file extension an empty string is returned.
     *
     * @param $path
     * @return string
     */
    static function getFileExtension($path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * This function returns the maxumim download size allowed in bytes
     * To Change this modify the php.ini file or use:
     * <code>
     *   ini_set('post_max_size');
     *   ini_set('upload_max_filesize')
     * </code>
     *
     * @return int
     */
    static function getMaxUploadSize()
    {
        $maxPost = self::string2Bytes(ini_get('post_max_size'));
        $maxUpload = self::string2Bytes(ini_get('upload_max_filesize'));
        if ($maxPost < $maxUpload) {
            return $maxPost;
        }
        return $maxUpload;
    }

    /**
     * Recursivly delete all files and directories from the given path
     *
     * @param string $path
     * @return bool
     */
    static function rmdir($path)
    {
        $path = self::ObjToStr($path);
        if ($path instanceof self) {
            $path = $path->pathname;
        }
        if (is_file($path)) {
            if (is_writable($path)) {
                if (@unlink($path)) {
                    return true;
                }
            }
            return false;
        }
        if (is_dir($path)) {
            if (is_writeable($path)) {
                foreach (new \DirectoryIterator($path) as $_res) {
                    if ($_res->isDot()) {
                        unset($_res);
                        continue;
                    }
                    if ($_res->isFile()) {
                        self::rmdir($_res->getPathName());
                    } elseif ($_res->isDir()) {
                        self::rmdir($_res->getRealPath());
                    }
                    unset($_res);
                }
                if (@rmdir($path)) {
                    return true;
                }
            }
            return false;
        }
    }



    /**
     * Checks whether the file or directory denoted by this pathname exists.
     *
     * @return bool
     */
    public function exists()
    {
        return file_exists($this->pathname);
    }

    /**
     * Returns file extension for this pathname.
     *
     * @return string
     */
    public function getExtension()
    {
        return self::getFileExtension($this->pathname);
    }

    /**
     * Returns the full pathname.
     *
     * @return string
     */
    public function getPathname()
    {
        return $this->pathname;
    }

    /**
     * Returns the size of the file in bytes.
     * If pathname does not exist or is not a file, 0 is returned.
     *
     * @return int
     */
    public function getSize()
    {
        if ($this->isFile()) {
            return filesize($this->pathname);
        }
        return 0;
    }

    /**
     * Checks whether this pathname is a directory.
     *
     * @return bool
     */
    public function isDir()
    {
        return is_dir($this->pathname);
    }

    /**
     * Checks whether this pathname is a regular file.
     *
     * @return bool
     */
    public function isFile()
    {
        return is_file($this->pathname);
    }

    /**
     * Checks whether this pathname is writable.
     *
     * @return bool
     */
    public function isWritable()
    {
        return is_writable($this->pathname);
    }

    /**
     * Checks whether this pathname is readable.
     *
     * @return bool
     */
    public function isReadable()
    {
        return is_readable($this->pathname);
    }

    /**
     * return the dirname of the path
     *
     * @return Tk_Path
     */
    public function getDirname()
    {
        return new self(dirname($this->pathname));
    }

    /**
     * Return the base name of the path
     *
     * @return string
     */
    public function getBasename()
    {
        return basename($this->pathname);
    }

    /**
     * Prepend a path to the main path
     *
     * @param string $pathname
     * @return Tk\Path
     */
    public function prepend($pathname)
    {
        $pathname = self::ObjToStr($pathname);
        if (substr($pathname, -1) == '/') {
            $pathname = substr($pathname, 0, -1);
        }
        if (substr($pathname, 0, 1) != '/' && !preg_match('/^[A-Za-z]:/', $pathname)) {
            $pathname = '/' . $pathname;
        }
        return new self($pathname . $this->toString());
    }

    /**
     * Append a path to the main path
     *
     * @param string $pathname
     * @return Tk\Path
     */
    public function append($pathname)
    {
        $pathname = self::ObjToStr($pathname);
        if (substr($pathname, -1) == '/') {
            $pathname = substr($pathname, 0, -1);
        }
        if (substr($pathname, 0, 1) != '/' && !preg_match('/^[A-Za-z]:/', $pathname)) {
            $pathname = '/' . $pathname;
        }
        return new self($this->toString() . $pathname);
    }

    /**
     * Get a string representation of this object
     * If $rel == true a relitive path to the site root path will be returned
     *
     * @param bool $rel
     * @return string
     */
    public function toString()
    {
        return $this->pathname;
    }

    /**
     * toRelativeString
     *
     * @return string
     */
    public function toRelativeString()
    {
        if (strstr($this->pathname, Config::getInstance()->getSitePath())) {
            return substr($this->pathname, strlen(Config::getInstance()->getSitePath()));
        }
        return $this->pathname;
    }

}