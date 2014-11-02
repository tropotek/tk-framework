<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Cache\Adapter;

/**
 *
 * <code>
 * <?php
 *   $ad = new Tk\Cache\MemCache();
 *   $ad->addServer('www1');
 *   $ad->addServer('www2',11211,20); // this server has double the memory, and gets double the weight
 *   $ad->addServer('www3',11211);
 *
 *   $cache = new Tk\Cache($ad);
 *   // Store some data in the cache for 10 minutes
 *   $cache->store('my_key','foobar',600);
 *
 *   // Get it out of the cache again
 *   echo($cache->fetch('my_key'));
 * ?>
 * </code>
 *
 * @package Tk\Cache\Adapter
 */
class MemCache extends \Tk\Object implements Iface
{

    // Memcache object
    public $connection;

    /**
     * construct
     *
     */
    public function __construct()
    {
        $this->connection = new MemCache();
    }

    /**
     * add memcache search server
     *
     * @param string $host
     * @param int $port
     * @param int $weight
     */
    public function addServer($host, $port = 11211, $weight = 10)
    {
        $this->connection->addServer($host, $port, true, $weight);
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
        return $this->connection->set($key, $data, 0, $ttl);
    }

    /**
     * Fetch
     *
     * @param string $key
     * @return mixed Returns false on fail
     */
    public function fetch($key)
    {
        return $this->connection->get($key);
    }

    /**
     * Delete
     *
     * @param string $key
     * @return bool
     */
    public function delete($key)
    {
        return $this->connection->delete($key);
    }

    /**
     * Clear the cache
     * TODO: Test if thats what flush means...????
     *
     * @return bool
     */
    public function clear()
    {
        return $this->connection->flush();
    }
}