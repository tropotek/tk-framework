<?php
namespace Tk\Cache\Adapter;

/**
 *
 * <code>
 * <?php
 *   $ad = new Tk\Cache\Adapter\MemCache();
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
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class MemCache implements Iface
{

    public \MemCache $connection;



    public function __construct()
    {
        $this->connection = new \MemCache();
    }

    /**
     * Adds a memcache search server
     */
    public function addServer(string $host, int $port = 11211, int $weight = 10)
    {
        $this->connection->addServer($host, $port, true, $weight);
    }


    /**
     * Store
     * @return bool Returns false if no value available
     */
    public function store(string $key, $data, int $ttl = 0)
    {
        return $this->connection->set($key, $data, 0, $ttl);
    }

    /**
     * Fetch
     *
     * @return string|array|false Returns false if no value available
     */
    public function fetch(string $key)
    {
        return $this->connection->get($key);
    }

    /**
     * Delete
     * @return bool Returns false if no value available
     */
    public function delete(string $key)
    {
        return $this->connection->delete($key);
    }

    /**
     * Clear the cache
     * TODO: Test if thats what flush means...????
     */
    public function clear(): bool
    {
        return $this->connection->flush();
    }
}