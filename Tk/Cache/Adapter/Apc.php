<?php
namespace Tk\Cache\Adapter;

/**
 * An Apc cache class
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class Apc implements Iface
{

    /**
     * Fetch
     *
     * @return mixed|false Returns false on fail
     */
    public function fetch(string $key)
    {
        return apc_fetch($key);
    }

    /**
     * Store
     *
     * @param mixed $data
     * @param int $ttl
     * @return array|bool
     */
    public function store(string $key, $data, int $ttl = 0)
    {
        return apc_store($key, $data, $ttl);
    }

    /**
     * Delete
     *
     * @return bool|\string[]
     */
    public function delete(string $key)
    {
        return apc_delete($key);
    }

    /**
     * Clears the cache
     *
     * @return bool true on success or false on failure.
     */
    public function clear(): bool
    {
        return apc_clear_cache();
    }

}