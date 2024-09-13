<?php
namespace Tk\Cache\Adapter;

/**
 * A PHP filesystem cache class
 * This adapter uses a 10 byte header to test the time therefore should be faster than having
 * to read the entire cache file
 *
 * Use this adapter to save sensitive cache data into an encoded serialized string
 * within a php file. Decreasing the risk of external access.
 *
 * @see http://www.rooftopsolutions.nl/blog/107
 */
class PhpFile extends Filesystem
{

    /**
     * @param int $ttl Time to live in seconds
     */
    public function store(string $key, mixed $data, int $ttl = 0): bool
    {
        $d = base64_encode(serialize($data));
        return parent::store($key, $d, $ttl);
    }

    public function fetch(string $key): mixed
    {
        $d = parent::fetch($key);
        return unserialize(base64_decode($d));
    }

    protected function getFileName(string $key): string
    {
        return $this->getCachePath() . '/' . $key . '.php';
    }

}
