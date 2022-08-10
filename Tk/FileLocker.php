<?php
namespace Tk;


/**
 * @author Michael Mifsud <http://www.tropotek.com/>
 * @link http://www.tropotek.com/
 * @license Copyright 2018 Michael Mifsud
 */
class FileLocker
{
    /**
     * @var array
     */
    protected static $locFiles = array();



    /**
     * @param $filename
     * @param bool $wait
     * @return bool|resource
     * @throws \Exception
     */
    public static function lockFile($filename, $wait = false)
    {
        $locFile = fopen($filename, 'c');
        if ( !$locFile ) {
            throw new \Exception('Can\'t create lock file!');
        }
        if ( $wait ) {
            $lock = flock($locFile, LOCK_EX);
        } else {
            $lock = flock($locFile, LOCK_EX | LOCK_NB);
        }
        if ( $lock ) {
            self::$locFiles[$filename] = $locFile;
            fprintf($locFile, "%s\n", getmypid());
            return $locFile;
        } else if ( $wait ) {
            throw new \Exception('Can\'t lock file!');
        } else {
            return false;
        }
    }

    /**
     * @param $filename
     */
    public static function unlockFile($filename)
    {
        if (empty(self::$locFiles[$filename])) return;
        fclose(self::$locFiles[$filename]);
        @unlink($filename);
        unset(self::$locFiles[$filename]);
    }

}