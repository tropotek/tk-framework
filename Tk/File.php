<?php
namespace Tk;

/**
 * Tools for dealing with filesystem data
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class File
{

    /**
     * Default location of the mime.types remote file
     * @var string
     */
    public static $MIME_TYPES_URL      = 'http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types';

    /**
     * @var string
     */
    public static $CACHE_MIME_FILE     = 'mime.types';

    /**
     * @var int
     */
    public static $CACHE_MIME_SEC      = 60 * 60 * 24 * 28;     // 28 day cache


    /**
     * Returns true if given $path is an absolute path.
     *
     * @param $pathname
     * @return bool true if path is absolute.
     */
    public static function isAbsolute($pathname)
    {
        return !empty($pathname) && ($pathname[0] === '/' || preg_match('/^[A-Z]:\\\\/i', $pathname) || substr($pathname, 0, 2) == '\\\\');
    }

    /**
     * Get the bytes from a string like 40M, 10T, 100K
     *
     * @param string $str
     * @return int
     */
    public static function string2Bytes($str)
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
     * @param int $round
     * @return string
     * @author http://php-pdb.sourceforge.net/samples/viewSource.php?file=twister.php
     */
    public static function bytes2String($bytes, $round = 2)
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
    public static function diskSpace($path)
    {
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
     * EG: 'mp3', 'php', ...
     *
     * @param $path
     * @return string
     */
    public static function getExtension($path)
    {
        if (substr($path, -6) == 'tar.gz') {
            return 'tar.gz';
        }
        $str = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return $str;
    }

    /**
     * remove the extension part of a filename
     *
     * @param string $path
     * @return string
     */
    public static function removeExtension($path)
    {
        $str = str_replace('.'.self::getExtension($path), '', $path);
        return $str;
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
    public static function getMaxUploadSize()
    {
        $maxPost = self::string2Bytes(ini_get('post_max_size'));
        $maxUpload = self::string2Bytes(ini_get('upload_max_filesize'));
        if ($maxPost < $maxUpload) {
            return $maxPost;
        }
        return $maxUpload;
    }

    /**
     * Copy the contents of a source directory
     * to the destination directory, the destination
     * will be created if not exists
     *
     * @param $source
     * @param $destination
     */
    public static function copyDir_stuffed($source, $destination)
    {
        if (!file_exists($destination)) {
            mkdir($destination, 0777, true);
        }

        $splFileInfoArr = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::SELF_FIRST);
        /** @var \SplFileInfo $splFileinfo */
        foreach ($splFileInfoArr as $fullPath => $splFileinfo) {
            if (in_array($splFileinfo->getBasename(), ['.', '..'])) {
                continue;
            }
            //get relative path of source file or folder
            $path = str_replace($source, '', $splFileinfo->getPathname());

            if (!file_exists(dirname($destination . '/' . trim($path, '/')))) {
               mkdir($destination . '/' . trim($path, '/'), 0777, true);
            }
            if (!$splFileinfo->isDir()) {
                copy($fullPath, $destination . '/' . trim($path, '/'));
            }
        }
    }

    /**
     * @param $src
     * @param $dst
     */
    public static function copyDir($src, $dst)
    {
        $dir = opendir($src);
        if (!file_exists($dst)) {
            @mkdir($dst);
        }
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    self::copyDir($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }



    /**
     * Recursively delete all files and directories from the given path
     *
     * @param string $path
     * @return bool
     */
    public static function rmdir($path)
    {
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
     * @param string $path
     * @param null|callable $onDelete
     */
    public static function removeEmptyFolders($path, $onDelete = null) {

        if(substr($path,-1)!= DIRECTORY_SEPARATOR){
            $path .= DIRECTORY_SEPARATOR;
        }
        $d2 = array('.','..');
        $dirs = array_diff(glob($path.'*', GLOB_ONLYDIR),$d2);
        foreach($dirs as $d){
            self::removeEmptyFolders($d);
        }

        if(count(array_diff(glob($path.'*'),$d2))===0){
            $conf = null;
            if (is_callable($onDelete))
                $conf = call_user_func_array($onDelete, array($path));
            if ($conf === true || $conf === null)
                rmdir($path);
        }

    }

    /**
     * @param string $path
     * @param callable $onDelete
     * @return bool
     * @deprecated Use the above function instead this one seemed to have issues
     */
    public static function removeEmptyFolders1($path, $onDelete = null)
    {
        $empty = true;
        foreach (glob($path . \DIRECTORY_SEPARATOR . '{,.}[!.,!..]*',GLOB_MARK | GLOB_BRACE) as $file) {
            $empty &= is_dir($file) && self::removeEmptyFolders1($file, $onDelete);
        }
        if ($empty) {
            $conf = null;
            if (is_callable($onDelete))
                $conf = call_user_func_array($onDelete, array($path));
            if ($conf === true || $conf === null)
                @rmdir($path);
        }
        return $empty;
    }

    /**
     * Get the mime type of a file based on its extension
     *
     * @param string $filename
     * @return string
         */
    public static function getMimeType($filename)
    {
        $mimeTypes = self::getMimeArray();
        $ext = self::getExtension($filename);
        if (array_key_exists($ext, $mimeTypes)) {
            return $mimeTypes[$ext];
        }
        if (is_readable($filename)) {
            if (function_exists('mime_content_type')) {     // Deprecated function in PHP
                return mime_content_type($filename);
            }
            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME);
                $mimetype = current(explode(';', finfo_file($finfo, $filename)));
                finfo_close($finfo);
                return $mimetype;
            }
        }

        // if all else fails
        return 'application/octet-stream';
    }

    /**
     * @return array
     */
    public static function getMimeArray() {
        $config = \Tk\Config::getInstance();        // Not good
        $mimeFile = self::$CACHE_MIME_FILE;
        if (strstr($mimeFile, $config->getSitePath()) == false)
            $mimeFile = $config->getDataPath() .'/'. trim(self::$CACHE_MIME_FILE, '/');
        $mimeFileContents = null;

        // Update Cache
        if (@is_file($mimeFile)) {
            if ((@filemtime($mimeFile) < (time() - self::$CACHE_MIME_SEC))) {
                $mimeFileContents = @file_get_contents(self::$MIME_TYPES_URL);
                if ($mimeFileContents !== false) {
                    @file_put_contents($mimeFile, $mimeFileContents);
                }
            }
            $mimeFileContents = @file_get_contents($mimeFile);
        } else {
            $mimeFileContents = @file_get_contents(self::$MIME_TYPES_URL);
            if ($mimeFileContents !== false) {
                @file_put_contents($mimeFile, $mimeFileContents);
            }
        }

        $s = array();
        foreach(@explode("\n", $mimeFileContents) as $x) {
            if (isset($x[0]) && $x[0] !== '#' && preg_match_all('#([^\s]+)#', $x, $out) && isset($out[1]) && ($c = count($out[1])) > 1)
                for ($i = 1; $i < $c; $i++) {
                    $s[$out[1][$i]] = $out[1][0];
                }
        }
        @ksort($s);
        return $s;
    }

    /**
     * Remove all spaces and special chars from a file/path name
     *
     * Warning: Do not send full paths only the folder or filename in question
     *
     * @param string $filename
     * @return string
     */
    public static function cleanFilename($filename)
    {
        $filename = basename($filename);
        $filename = preg_replace('/^([^a-zA-Z0-9\s-]+)$/', '_', $filename);
        return $filename;
    }
}