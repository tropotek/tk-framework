<?php
namespace Tk\Cache\Adapter;

use Tk\FileUtil;

/**
 * A filesystem cache class
 * This adapter uses a 10 byte header to test the time therefore should be faster than having
 * to read the entire cache file
 *
 * @see http://www.rooftopsolutions.nl/blog/107
 */
class Filesystem implements Iface
{
    public static int $DIR_MASK = 0777;

    /**
     * @var string
     */
    protected string $cachePath = '';


    public function __construct(string $cachePath = '')
    {
        $this->cachePath = $cachePath;
    }

    public function store(string $key, mixed $data, int $ttl = 0): bool
    {
        if (!FileUtil::mkdir($this->getCachePath())) {
            throw new \Tk\Cache\Exception('Cannot create path: ' . $this->getCachePath());
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
        fseek($h,strlen(time()));
        // Serializing along with the TTL
        $data = serialize($data);
        if (fwrite($h, $data) === false) {
            throw new \Tk\Cache\Exception('Could not write to cache');
        }
        fclose($h);
        return true;
    }

    public function fetch(string $key): mixed
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
        $ttl = fread($h, strlen(time()));
        if (time() > $ttl) {
            // Unlinking when the file was expired
            fclose($h);
            unlink($filename);
            return false;
        }
        $data = file_get_contents($filename, false, null, strlen(time()));
        fclose($h);

        $data = @unserialize($data);
        if (!$data) {     // If un serializing somehow didn't work out, we'll delete the file
            unlink($filename);
            return false;
        }
        return $data;
    }

    public function delete(string $key): bool
    {
        $filename = $this->getFileName($key);
        if (file_exists($filename)) {
            return unlink($filename);
        } else {
            return false;
        }
    }

    public function clear(): bool
    {
        return \Tk\FileUtil::rmdir($this->getCachePath());
    }

    private function getFileName(string $key): string
    {
        return $this->getCachePath() . '/' . $key;
    }

    public function getCachePath(): string
    {
        return $this->cachePath;
    }

}
