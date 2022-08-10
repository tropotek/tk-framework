<?php
namespace Tk;


/**
 * Class CollectionTrait
 *
 * @author Michael Mifsud <http://www.tropotek.com/>
 * @see http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
trait CollectionTrait
{
    
    /**
     * @var Collection
     */
    protected $collection = null;


    /**
     * 
     * @return Collection
     */
    public function getCollection()
    {
        if (!$this->collection) {
            $this->collection = new Collection();
        }
        return $this->collection;
    }
    
    /**
     * Set an item in the collection
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function set($key, $value)
    {
        $this->getCollection()->set($key, $value);
        return $this;
    }

    /**
     * Get collection item for key
     * 
     * @param string $key
     * @param mixed $default Return value if the key does not exist
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->getCollection()->get($key, $default);
    }

    /**
     * Does this collection have a given key?
     *
     * @param string $key The data key
     * @return bool
     */
    public function has($key)
    {
        return $this->getCollection()->has($key);
    }

    /**
     * Remove item from collection
     *
     * @param string $key The data key
     * @return $this
     */
    public function remove($key)
    {
        $this->getCollection()->remove($key);
        return $this;
    }

    /**
     * Add a list of items to the collection
     *
     * @param array|Collection $items Key-value array of data to append to this collection
     * @return $this
     */
    public function replace($items)
    {
        $this->getCollection()->replace($items);
        return $this;
    }

    /**
     * Get all items from the collection
     *
     * @return array
     */
    public function all()
    {
        return $this->getCollection()->all();
    }
    
}