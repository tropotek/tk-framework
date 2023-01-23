<?php
namespace Tk;

/**
 * Tools for dealing with filesystem data
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class FileUtil
{
    public static $DIR_MASK = 0777;

    /**
     * Default location of the mime.types remote file
     * @var string
     */
    public static $MIME_TYPES_URL      = 'http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types';

    /**
     * @var string
     */
    public static $CACHE_MIME_FILE     = ''; // TODO '/data/cache/mime.types';

    /**
     * @var int
     */
    public static $CACHE_MIME_SEC      = 60 * 60 * 24 * 28;     // 28 day cache


    /**
     * No instance of this object is allowed
     */
    private function __construct() {}

    /**
     * Returns true if the given $pathname is an absolute path.
     */
    public static function isAbsolute(string $pathname): bool
    {
        return !empty($pathname) && ($pathname[0] === '/' || preg_match('/^[A-Z]:\\\\/i', $pathname) || substr($pathname, 0, 2) == '\\\\');
    }

    /**
     * Most of the time the system mkdir calls want to check if the dir exists if not
     * create it and all parent folders if they do not exist.
     *
     * @return bool Returns true on success ot if path exists and false on failure
     */
    public static function mkdir(string $directory, bool $recursive = true): bool
    {
        if (!is_dir($directory))
            return mkdir($directory, self::$DIR_MASK, $recursive);
        return true;
    }

    /**
     * Get the bytes from a string like 40M, 10T, 100K
     */
    public static function string2Bytes(string $str): int
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
     * @author http://php-pdb.sourceforge.net/samples/viewSource.php?file=twister.php
     */
    public static function bytes2String(int $bytes, int $round = 2): string
    {
        $tags = ['b', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
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
     * any of the other suggestions, but I think they are much more realistic
     *
     */
    public static function diskSpace(string $path): int
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
     * At the last period ('.') in the pathname is used to delimit the file
     * extension. If the pathname does not have a file extension an empty string is returned.
     * EG: 'mp3', 'php', ...
     */
    public static function getExtension(string $path): string
    {
        if (substr($path, -6) == 'tar.gz') {
            return 'tar.gz';
        }
        $str = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return $str;
    }

    /**
     * Remove the extension part of a filename
     */
    public static function removeExtension(string $path): string
    {
        $str = str_replace('.'.self::getExtension($path), '', $path);
        return $str;
    }


    /**
     * This function returns the maxumim download size allowed in bytes
     * To Change this, modify the php.ini file or use:
     * <code>
     *   ini_set('post_max_size');
     *   ini_set('upload_max_filesize')
     * </code>
     */
    public static function getMaxUploadSize(): int
    {
        $maxPost = self::string2Bytes(ini_get('post_max_size'));
        $maxUpload = self::string2Bytes(ini_get('upload_max_filesize'));
        if ($maxPost < $maxUpload) {
            return $maxPost;
        }
        return $maxUpload;
    }

    /**
     *
     */
    public static function copyDir(string $src, string $dst)
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
     */
    public static function rmdir(string $path): bool
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
     * Remove all empty folders from a path.
     *
     * @param callable|null $onDelete Add a callable here if you want to perfom an action before deletion.
     */
    public static function removeEmptyFolders(string $path, ?callable $onDelete = null)
    {
        if(substr($path,-1)!= DIRECTORY_SEPARATOR){
            $path .= DIRECTORY_SEPARATOR;
        }
        $d2 = ['.','..'];
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
     * Get the mime-type of a file based on its extension
     *
     */
    public static function getMimeType(string $filename): string
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
     *
     */
    public static function getMimeArray(): array
    {
        $mimeFile = self::$CACHE_MIME_FILE;
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

        $s = [];
        foreach(@explode("\n", $mimeFileContents) as $x) {
            if (isset($x[0]) && $x[0] !== '#' && preg_match_all('#([^\s]+)#', $x, $out) && isset($out[1]) && ($c = count($out[1])) > 1)
                for ($i = 1; $i < $c; $i++) {
                    $s[$out[1][$i]] = $out[1][0];
                }
        }
        @ksort($s);
        return $s;
    }

}