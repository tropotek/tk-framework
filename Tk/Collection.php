<?php

namespace Tk;


/**
 * Class Collection
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 * @see http://git.snooey.net/Mirrors/php-slim/
 */
class Collection implements \ArrayAccess, \IteratorAggregate, \Countable
{

    protected $data = array();


    /**
     * Collection constructor.
     *
     * @param array $items
     */
    public function __construct($items = array())
    {
        foreach ($items as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Add a list of items to the collection
     *
     * @param array|Collection $items Key-value array of data to append to this collection
     * @return $this
     */
    public function replace($items)
    {
        if ($items instanceof Collection) {
            $items = $items->all();
        }
        if (is_array($items)) {
            foreach ($items as $key => $value) {
                $this->set($key, $value);
            }
        }
        return $this;
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
        $this->data[$key] = $value;
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
        return $this->has($key) ? $this->data[$key] : $default;
    }

    /**
     * Get all items in collection
     *
     * @param null|string|array $regex
     * @return array The collection's source data
     */
    public function all($regex = null)
    {
        if ($regex) {
            $array = array();
            foreach ($this->data as $name => $value) {
                if (is_string($regex) && !preg_match($regex, $name)) {
                    continue;
                } else if (is_array($regex) && !in_array($name, $regex)) {
                    continue;
                }
                $array[$name] = $value;
            }
            return $array;
        }
        return $this->data;
    }

    /**
     * Get collection keys
     *
     * @return array The collection's source data keys
     */
    public function keys()
    {
        return array_keys($this->data);
    }

    /**
     * Does this collection have a given key?
     *
     * @param string $key The data key
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Remove item from collection
     *
     * @param string $key The data key
     * @return $this
     */
    public function remove($key)
    {
        unset($this->data[$key]);
        return $this;
    }

    /**
     * Remove all items from collection
     *
     * @return $this
     */
    public function clear()
    {
        $this->data = array();
        return $this;
    }


    /**
     * Does this collection have a given key?
     *
     * @param  string $key The data key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * Get collection item for key
     *
     * @param string $key The data key
     *
     * @return mixed The key's value, or the default value
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Set collection item
     *
     * @param string $key The data key
     * @param mixed $value The data value
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Remove item from collection
     *
     * @param string $key The data key
     */
    public function offsetUnset($key)
    {
        $this->remove($key);
    }

    /**
     * Get number of items in collection
     *
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * Get collection iterator
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->all();
    }

    /**
     * Use this to return the items in an array that match the expression
     *
     * @param $array
     * @param $regex
     * @return array
     */
    public static function arrayKeyRegex($array, $regex)
    {
        $a = array();
        foreach ($array as $name => $value) {
            if (!preg_match($regex, $name)) continue;
            $a[$name] = $value;
        }
        return $a;
    }

    /**
     * flatten a multi-dimensional array to a single-dimensional array
     * NOTE: All key values will be lost.
     *
     * @param array $array
     * @return array
     */
    public static function arrayFlatten($array)
    {
        $return = array();
        array_walk_recursive($array, function($a) use (&$return) { if ($a !== null) $return[] = $a; });
        return $return;
    }

    /**
     * Return the difference of 2 multidimensinal arrays
     * If no difference null is returned.
     *
     * @param array $array1
     * @param array $array2
     * @return null|array   Returns null if there are no differences
     * @site http://php.net/manual/en/function.array-diff-assoc.php
     * @author telefoontoestel at hotmail dot com
     */
    public static function arrayDiffRecursive($array1, $array2)
    {
        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (!isset($array2[$key])) {
                    $difference[$key] = $value;
                } elseif (!is_array($array2[$key])) {
                    $difference[$key] = $value;
                } else {
                    $new_diff = self::arrayDiffRecursive($value, $array2[$key]);
                    if ($new_diff != false) {
                        $difference[$key] = $new_diff;
                    }
                }
            } elseif (!array_key_exists($key, $array2) || $array2[$key] != $value) {
                $difference[$key] = $value;
            }
        }
        return !isset($difference) ? null : $difference;
    }


    /**
     * prefix a string to all array keys
     *
     * @param array $array
     * @param string $prefix
     * @return array
     */
    public static function prefixArrayKeys(array $array, string $prefix)
    {
        if ($prefix != '' && is_string($prefix)) {
            foreach ($array as $k => $v) {
                $array[$prefix . $k] = $v;
                unset($array[$k]);
            }
        }
        return $array;
    }

    /**
     * Return a readable string representation of this object
     *
     * @param $arr
     * @return string
     */
    public static function arrayToString($arr)
    {
        $str = "";
        foreach ($arr as $k => $v) {
            if (is_object($v)) {
                $str .= "[$k] => {" . get_class($v) . "}\n";
            } elseif (is_array($v)) {
                $str .= "[$k] =>  array[" . count($v) . "]\n";
            } else {
                $str .= "[$k] => $v\n";
            }
        }
        return $str;

    }

    /**
     * toString
     *
     * @return string
     */
    public function toString()
    {
        $arr = self::arrayToString($this->data);
        //ksort($arr);
        return $arr;
    }

}