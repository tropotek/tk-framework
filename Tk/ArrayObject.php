<?php

/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

namespace Tk;

/**
 * A wrapper for the php array object
 * This object also contains all the array functions as methods.
 *
 * @package Tk
 */
class ArrayObject extends Object implements \IteratorAggregate, \ArrayAccess, \Serializable, \Countable
{

    /**
     * @var array
     */
    private $data = array();

    /**
     *
     * @param array $data
     */
    public function __construct($data = array())
    {
        $this->data = (array)$data;
    }

    /**
     * create
     *
     * @param array $data
     * @return \Tk\ArrayObject
     */
    static function createArray($data = array())
    {
        $obj = new self($data);
        return $obj;
    }

    /**
     * Return the native data array
     *
     * @return array
     */
    public function getArray()
    {
        return $this->data;
    }

    /**
     * Load an array of keys with values from this array.
     * Handy to get a subset of values and set them in a new array
     *
     * @param array $array
     */
    public function loadArray($array = array())
    {
        foreach ($array as $k => $v) {
            if ($this->exists($k))
                $array[$k] = $v;
        }
    }

    /**
     * merge an external array into this array
     *
     * @param array $src
     * @return array
     */
    public function mergeArray($src)
    {
        if ($this === $src) return $this;
        if ($src instanceof \Tk\ArrayObject) {
            $src = $src->getArray();
        }
        $this->data = array_merge($this->data, $src);
        return $this;
    }

    /**
     * merge an external array into this array
     * Alias for meargeArray()
     *
     * @param array $src
     * @return array
     */
    public function import($src)
    {
        return $this->mergeArray($src);
    }

    /**
     * Exchange the array for another one.
     * If no array supplied the current data array returned.
     *
     * @param array $array
     * @return array
     */
    public function exchangeArray(array $array = null)
    {
        $ret = $this->data;
        if ($array) {
            $this->data = $array;
        }
        return $ret;
    }








    /**
     *  \IteratorAggregate
     *
     * @return \Tk\ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    /**
     * \ArrayAccess
     *
     * @param type $offset
     * @param type $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
        $this->notify('set_' . $offset);
        $this->notify($offset);
    }

    /**
     * \ArrayAccess
     *
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        $this->notify('exists_' . $offset);
        return isset($this->data[$offset]);
    }

    /**
     * \ArrayAccess
     *
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        $this->notify('unset_' . $offset);
        unset($this->data[$offset]);
    }

    /**
     * \ArrayAccess
     *
     * @param string $offset
     * @return bool
     */
    public function offsetGet($offset)
    {
        $this->notify('get_' . $offset);
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    /**
     * \Serializable
     *
     * @return string
     */
    public function serialize()
    {
        return serialize($this->data);
    }

    /**
     * \Serializable
     *
     * @param string $data
     */
    public function unserialize($data)
    {
        $this->data = unserialize($data);
    }

    /**$name
     * \Countable
     *
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * Allow for the items to be treated as object params
     * EG: $arrObj->item = $arrObj['item']
     *
     * @param $key
     * @param mixed $val
     * @return \Tk\ArrayObject
     */
    public function set($key, $val)
    {
        $this->offsetSet($key, $val);
        return $this;
    }

    /**
     * Set an entry into the registry cache if not exist
     *
     * @param string $key
     * @param mixed $val
     * @return \Tk\ArrayObject
     */
    public function nset($key, $val)
    {
        if (!$this->offsetExists($key)) {
            $this->offsetSet($key, $val);
        }
        return $this;
    }

    /**
     * Allow for the items to be treated as object params
     * EG: $arrObj->item = $arrObj['item']
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->offsetGet($key);
    }

    /**
     * Test if an array key exists in this object
     *
     * @param string $key
     * @return bool
     */
    public function exists($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Remove an entry from the registry cache
     *
     * @param string $key
     * @return \Tk\Registry
     */
    public function delete($key)
    {
        $this->offsetUnset($key);
        return $this;
    }

    /**
     * Allow for the items to be treated as object params
     * EG: $arrObj->item = $arrObj['item']
     *
     * @param string $key
     * @param mixed $val
     */
    public function __set($key, $val)
    {
        $this->offsetSet($key, $val);
    }

    /**
     * Allow for the items to be treated as object params
     * EG: $arrObj->item = $arrObj['item']
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->offsetGet($key);
    }

    /**
     * Allow calls to array_* functions:
     * <?php
     *      $yourObject->array_keys();
     *      OR
     *      $yourObject->arrayKeys();
     * ?>
     * Don't forget to ommit the first parameter - it's automatic!
     *
     * @param string $func
     * @param array $argv
     * @return \Tk\ArrayObject
     * @throws \Tk\Exception
     */
    public function __call($func, $argv)
    {
        if (!strpos($func, '_')) {
            $func = preg_replace('/[A-Z]/', '_$0', $func);
            $func = strtolower($func);
        }
        if (!is_callable($func) || substr($func, 0, 5) !== 'array') {
            throw new Exception('Method not found: ' . __CLASS__ . '->' . $func);
        }
        return call_user_func_array($func, array_merge(array($this->data), $argv));
        //return call_user_func_array($func, array_merge(array($this->getArrayCopy()), $argv));
    }

    /**
     * toString
     *
     * @return string
     */
    public function toString()
    {
        $str = "";
        $arr = $this->data;
        ksort($arr);
        foreach ($arr as $k => $v) {
            if (is_object($v)) {
                $str .= "[$k] => { " . get_class($v) . "}\n";
            } elseif (is_array($v)) {
                $str .= "[$k] =>  array[" . count($v) . "]\n";
            } else {
                $str .= "[$k] => $v\n";
            }
        }
        return $str;
    }

    /**
     * __toString
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

}