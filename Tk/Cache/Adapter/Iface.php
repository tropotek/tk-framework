<?php
namespace Tk\Cache\Adapter;

/**
 * Cache Adapter Interface
 */
interface Iface
{
    /**
     * Fetch
     */
    public function fetch(string $key): mixed;

    /**
     * Store
     */
    public function store(string $key, mixed $data, int $ttl = 0): mixed;

    /**
     * Delete
     */
    public function delete(string $key): mixed;

    /**
     * Delete all cached values
     */
    public function clear(): bool;
}
