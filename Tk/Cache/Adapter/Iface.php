<?php
namespace Tk\Cache\Adapter;

/**
 * Cache Adapter Interface
 *
 * @author <http://www.tropotek.com/>
 */
interface Iface
{
    /**
     * Fetch
     *
     * @return mixed Returns false if no value available
     */
    public function fetch(string $key);

    /**
     * Store
     * @param mixed $data
     * @return mixed Returns false if no value available
     */
    public function store(string $key, $data, int $ttl = 0);

    /**
     * Delete
     * @return mixed Returns false if no value available
     */
    public function delete(string $key);

    /**
     * Delete all cached values
     *
     * @return bool
     */
    public function clear(): bool;
}
