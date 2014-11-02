<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Cache\Adapter;

/**
 * A Apc cache class
 *
 * @package Tk\Cache\Adapter
 */
class Apc extends \Tk\Object implements Iface
{

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
     */
    public function store($key, $data, $ttl = 0)
    {
        return apc_store($key, $data, $ttl);
    }

    /**
     * Delete
     *
     * @param string $key
     */
    public function delete($key)
    {
        return apc_delete($key);
    }

    /**
     *
     * Clears the cache
     *
     * @param string $cache_type [optional] <p>
     * The system cache (cached files) will be cleared.
     * </p>
     * @return bool true on success or false on failure.
     */
    public function clear()
    {
        return apc_clear_cache();
    }

}