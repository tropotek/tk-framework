<?php
namespace Tk\Cache\Adapter;



/**
 * A filesystem cache class
 * This adapter uses a 10 byte header to test the time therefore should be faster than having
 * to read the entire cache file
 *
 * @see http://www.rooftopsolutions.nl/blog/107
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Filesystem implements Iface
{
    /**
     * @var string
     */
    protected $cachePath = '';
    

    /**
     * __construct
     *
     * @param string $cachePath
     */
    public function __construct($cachePath = '')
    {
        $this->cachePath = $cachePath;
    }

    /**
     * This is the function you store information with
     *
     * @param string $key
     * @param mixed $data
     * @param int $ttl
     * @return bool|void
     * @throws \Tk\Exception
     */
    public function store($key, $data, $ttl = 0)
    {
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, \Tk\Config::getInstance()->getDirMask(), true);
        }
        // Opening the file in read/write mode
        $h = fopen($this->getFileName($key), 'a+');
        if (!$h) {
            throw new \Tk\Cache\Exception('Could not write to cache');
        }
        flock($h, \LOCK_EX); // exclusive lock, will get released when the file is closed
        fseek($h, 0); // go to the start of the file
        // truncate the file
        ftruncate($h, 0);
        fwrite($h,time()+$ttl);
        fseek($h,10);
        // Serializing along with the TTL
        $data = serialize($data);
        if (fwrite($h, $data) === false) {
            throw new \Tk\Cache\Exception('Could not write to cache');
        }
        fclose($h);
    }

    /**
     * The function to fetch data returns false on failure
     *
     * @param string $key
     * @return bool
     */
    public function fetch($key)
    {
        $filename = $this->getFileName($key);
        if (!file_exists($filename)) {
            return false;
        }
        $h = fopen($filename, 'r');
        if (!$h) {
            return false;
        }
        // Getting a shared lock
        flock($h, \LOCK_SH);
        $ttl = fread($h, 10);
        if (time() > $ttl) {
            // Unlinking when the file was expired
            fclose($h);
            unlink($filename);
            return false;
        }
        $data = file_get_contents($filename, null, null, 10);
        fclose($h);

        $data = @unserialize($data);
        if (!$data) {     // If unserializing somehow didn't work out, we'll delete the file
            unlink($filename);
            return false;
        }
        return $data;
    }

    /**
     * Delete
     *
     * @param string $key
     * @return bool
     */
    public function delete($key)
    {
        $filename = $this->getFileName($key);
        if (file_exists($filename)) {
            return unlink($filename);
        } else {
            return false;
        }
    }

    /**
     * Delete all files in the cachePath
     *
     * @return bool
     */
    public function clear()
    {
        return \Tk\File::rmdir($this->cachePath);
    }

    /**
     * Get cache filename with path
     *
     * @param string $key
     * @return string
     */
    private function getFileName($key)
    {
        return $this->cachePath . '/s_cache-' . md5($key);
    }

    /**
     * @return string
     */
    public function getCachePath()
    {
        return $this->cachePath;
    }

}
