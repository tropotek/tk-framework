<?php
namespace Tk\Cache;

/**
 * A Cache controller
 *
 *
 * <code>
 * <?php
 * // constructing our cache engine
 * $cache = new Tk\Cache(new Tk\Cache\Adapter\Filesystem());
 *
 * function getUsers() {
 *   global $cache;
 *   // A somewhat unique key
 *   $key = 'getUsers:selectAll';
 *   // check if the data is not in the cache already
 *   if (!$data = $cache->fetch($key)) {
 *       // there was no cache version, we are fetching fresh data
 *       // assuming there is a database connection
 *       $result = mysql_query("SELECT * FROM users");
 *       $data = array();
 *       // fetching all the data and putting it in an array
 *       while($row = mysql_fetch_assoc($result)) { $data[] = $row; }
 *       // Storing the data in the cache for 10 minutes
 *       $cache->store($key, $data, 600);
 *   }
 *   return $data;
 * }
 * $users = getUsers();
 * ?>
 * </code>
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 * 
 * @todo Move this to its own lib ?????
 */
class Cache
{

    /**
     * @var Adapter\Iface
     */
    protected $adapter = null;

    /**
     * @var bool
     */
    protected $enabled = true;

    
    /**
     * __construct
     *
     * @param Adapter\Iface $adapter
     */
    public function __construct(Adapter\Iface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Enable/Disable the cache
     *
     * @param bool $b
     */
    public function setEnabled($b = true)
    {
        $this->enabled = $b;
    }


    /**
     * Get the currently applied cache adapter
     *
     * @return \Tk\Cache\Adapter\Iface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Cache a variable in the data store
     *
     * @param string $key <p>
     * Store the variable using this name. <i>key</i>s are
     * cache-unique, so storing a second value with the same
     * <i>key</i> will overwrite the original value.
     * </p>
     * @param $data
     * @param int $ttl [optional] <p>
     * Time To Live; store <i>var</i> in the cache for
     * <i>ttl</i> seconds. After the
     * <i>ttl</i> has passed, the stored variable will be
     * expunged from the cache (on the next request). If no <i>ttl</i>
     * is supplied (or if the <i>ttl</i> is
     * 0), the value will persist until it is removed from
     * the cache manually, or otherwise fails to exist in the cache (clear,
     * restart, etc.).
     * </p>
     * @internal param mixed $var <p>
     * The variable to store
     * </p>
     * @return bool true on success or false on failure.
     * Store
     */
    public function store($key, $data, $ttl = 0)
    {
        return $this->adapter->store($key, $data, $ttl);
    }

    /**
     * Fetch a stored variable from the cache
     *
     * @param mixed $key <p>
     * The <i>key</i> used to store the value (with
     * <b>apc_store</b>). If an array is passed then each
     * element is fetched and returned.
     * </p>
     * @internal param bool $success [optional] <p>
     * Set to true in success and false in failure.
     * </p>
     * @return mixed The stored variable or array of variables on success; false on failure
     */
    public function fetch($key)
    {
        //if (!$this->enabled) return false;
        return $this->adapter->fetch($key);
    }

    /**
     * Deletes files from the opcode cache
     *
     * @param $key
     * @internal param mixed $keys <p>
     * The files to be deleted. Accepts a string,
     * array of strings, or an <b>APCIterator</b>
     * object.
     * </p>
     * @return mixed true on success or false on failure.
     * Or if <i>keys</i> is an array, then
     * an empty array is returned on success, or an array of failed files
     * is returned.
     */
    public function delete($key)
    {
        return $this->adapter->delete($key);
    }

    /**
     *
     * Clears the cache
     *
     * @internal param string $cache_type [optional] <p>
     * The system cache (cached files) will be cleared.
     * </p>
     * @return bool true on success or false on failure.
     */
    public function clear()
    {
        return $this->adapter->clear();
    }
}
