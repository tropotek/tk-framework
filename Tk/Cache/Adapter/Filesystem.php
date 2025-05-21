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
    protected string $cachePath = '';


    public function __construct(string $cachePath = '')
    {
        $this->cachePath = $cachePath;
    }

    /**
     * @param int $ttl Time to live in seconds
     */
    public function store(string $key, mixed $data, int $ttl = 0): bool
    {
        if (!FileUtil::mkdir($this->getCachePath())) {
            throw new \Tk\Exception('Cannot create path: ' . $this->getCachePath());
        }

        // Opening the file in read/write mode
        $h = fopen($this->getFileName($key), 'a+');
        if (!$h) {
            throw new \Tk\Exception('Could not write to cache');
        }

        flock($h, \LOCK_EX); // exclusive lock, will get released when the file is closed
        fseek($h, 0);           // go to the start of the file
        ftruncate($h, 0);        // truncate the file

        $ttl = ($ttl > 0) ? time()+$ttl : 0;

        // Serializing along with the TTL
        $store = serialize([
            'ttl' => $ttl,
            'data' => $data,
        ]);

        if (fwrite($h, $store) === false) {
            throw new \Tk\Exception('Could not write to cache');
        }

        fclose($h);
        return true;
    }

    public function fetch(string $key): mixed
    {
        $filename = $this->getFileName($key);

        if (!is_file($filename)) {
            return false;
        }

        $h = fopen($filename, 'r');
        if ($h === false) {
            return false;
        }

        $store = strval(file_get_contents($filename));
        $store = @unserialize($store);
        if ($store === false) {
            error_log("failed to unserialize cached data for key {$key}");
            unlink($filename);
            return false;
        }

        $ttl = $store['ttl'];
        if ($ttl != 0 && time() > $ttl) {
            unlink($filename);
            return false;
        }

        return $store['data'];
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

    public function purge(): bool
    {
        return \Tk\FileUtil::rmdir($this->getCachePath());
    }

    protected function getFileName(string $key): string
    {
        return $this->getCachePath() . '/' . $key;
    }

    public function getCachePath(): string
    {
        return $this->cachePath;
    }

}
