<?php

/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Cache\Adapter;

/**
 * A controller interface
 *
 * @package Tk\Cache\Adapter
 */
interface Iface
{
    /**
     * Fetch
     * Returns false if no cache available
     *
     * @param string $key
     * @return mixed
     */
    public function fetch($key);

    /**
     * Store
     *
     * @param string $key
     * @param string $data
     * @param int $ttl Default 24hrs
     * @return bool
     */
    public function store($key, $data, $ttl = 0);

    /**
     * Delete
     *
     * @param string $key
     * @return bool
     */
    public function delete($key);

    /**
     * Delete all cachefiles
     *
     * @return bool
     */
    public function clear();
}
