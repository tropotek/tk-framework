<?php
namespace Tk\Cache\Adapter;

/**
 * An Apc cache class
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Apc implements Iface
{

    /**
     * @return Apc
     */
    public static function create()
    {
        $obj = new static();
        return $obj;
    }

    /**
     * Fetch
     *
     * @param string $key
     * @return mixed Returns false on fail
     */
    public function fetch($key)
    {
        return apc_fetch($key);
    }

    /**
     * Store
     *
     * @param string $key
     * @param string $data
     * @param int $ttl
     * @return array|bool
     */
    public function store($key, $data, $ttl = 0)
    {
        return apc_store($key, $data, $ttl);
    }

    /**
     * Delete
     *
     * @param string $key
     * @return bool|\string[]
     */
    public function delete($key)
    {
        return apc_delete($key);
    }

    /**
     * Clears the cache
     *
     * @return bool true on success or false on failure.
     */
    public function clear()
    {
        return apc_clear_cache();
    }

}