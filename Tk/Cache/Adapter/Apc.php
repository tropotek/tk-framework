<?php
namespace Tk\Cache\Adapter;

/**
 * An Apc cache class
 */
class Apc implements Iface
{

    public function fetch(string $key): mixed
    {
        return apc_fetch($key);
    }

    public function store(string $key, mixed $data, int $ttl = 0): array|bool
    {
        return apc_store($key, $data, $ttl);
    }

    public function delete(string $key): array|bool
    {
        return apc_delete($key);
    }

    public function clear(): bool
    {
        return apc_clear_cache();
    }

}