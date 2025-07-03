<?php
namespace Tk\Cache\Adapter;

use Tk\FileUtil;
use Tk\Log;

/**
 * A serialised cache class.
 * uses PHPs `serialize()` function to store cache data
 */
class Serial implements Iface
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
            Log::error('Cannot create path: ' . $this->getCachePath());
            return false;
        }

        // Serializing along with the TTL
        $store = serialize([
            'ttl' => ($ttl > 0) ? time()+$ttl : 0,
            'data' => $data,
        ]);

        if (false === file_put_contents($this->getFileName($key), $store, LOCK_EX)) {
            return false;
        }

        return true;
    }

    public function fetch(string $key): mixed
    {
        $filename = $this->getFileName($key);

        if (!is_file($filename)) {
            return false;
        }

        $store = file_get_contents($filename);
        if ($store === false) {
            throw new \Tk\Exception('Could not read from cache');
        }

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

    public function getFileName(string $key): string
    {
        return $this->getCachePath() . '/' . $key;
    }

    public function getCachePath(): string
    {
        return $this->cachePath;
    }

}
