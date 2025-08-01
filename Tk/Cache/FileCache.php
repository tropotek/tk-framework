<?php

namespace Tk\Cache;

use Tk\FileUtil;
use Tk\Log;

/**
 * Cache text in a file using its file modified time to trigger the cache refresh.
 *
 *  <code>
 *  <?php
 *    // create a cache with a store of 10 min
 *    $cache = new FileCache('/path/to/cache/folder', 600)
 *
 *    // cache filename
 *    $file = 'cachefile.css';
 *
 *    // check if the data is not in the cache already
 *    if (!$css = $cache->fetch($file)) {
 *        // there was no cache version, we are fetching fresh data
 *        // assuming there is a database connection
 *        $css = "body { background-color: #EFEFEF; }";
 *        // Storing the data in the cache
 *        $cache->store($file, $css);
 *    }
 *
 *    // use the data
 *    echo sprintf('<style>%s</style>', $css);
 *  ?>
 *  </code>
 */
class FileCache
{
    protected string $cachePath = '';
    protected ?int $ttlSec = null;

    /**
     * A TTL value of `null` will keep the data cached forever until deleted
     */
    public function __construct(string $cachePath = '', ?int $ttlSec = 3600)
    {
        $this->cachePath = $cachePath;
        $this->ttlSec = $ttlSec;
    }

    /**
     * Store
     */
    public function store(string $filename, string $data): bool
    {
        if (!is_dir($this->cachePath)) {
            FileUtil::mkdir($this->cachePath);
            Log::notice("created cache folder: " . $this->cachePath);
        }

        $path = $this->getFileName($filename);
        if (false === file_put_contents($path, $data, LOCK_EX)) {
            return false;
        }
        return true;
    }

    /**
     * Fetch
     * returns null if cache not found or timed out
     */
    public function fetch(string $filename): false|string
    {
        $path = $this->getFileName($filename);
        if (!is_file($path)) {
            return false;
        }

        if ($this->ttlSec != null) {
            clearstatcache();
            $ftime = filemtime($path);
            if ($ftime < (time() - $this->ttlSec)) {
                unlink($path);
                return false;
            }
        }

        return file_get_contents($path);
    }

    /**
     * Delete
     */
    public function delete(string $filename): bool
    {
        $path = $this->getFileName($filename);
        if (!is_file($path)) return false;
        return unlink($path);
    }

    public function getFileName(string $filename): string
    {
        return $this->cachePath . '/' . basename($filename);
    }

}