<?php
namespace Tk\Util;

/**
 * A wrapper for the php array object
 *
 * This object also contains all the array functions as methods.
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
class ArrayObject implements \IteratorAggregate, \ArrayAccess, \Serializable, \Countable
{

    /**
     * @var array
     */
    private $array = array();



    /**
     *
     * @param array $array
     */
    public function __construct($array = array())
    {
        $this->array = (array)$array;
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
     * Return the native data array
     *
     * @return array
     */
    public function getDataArray()
    {
        return $this->array;
    }


    public function setDataArray($array)
    {
        if ($array instanceof ArrayObject) {
            $array = $array->getDataArray();
        }
        if (!is_array($array)) {
            throw new \UnexpectedValueException('Parameter not of type array or ' . get_class($this));
        }
        $this->array = $array;
        return $this;
    }

    /**
     * merge an external array into this array
     *
     * @param array $array
     * @return ArrayObject
     */
    public function merge($array)
    {
        if ($array instanceof ArrayObject) {
            $array = $array->getDataArray();
        }
        if (!is_array($array)) {
            throw new \UnexpectedValueException('Parameter not of type array or ' . get_class($this));
        }
        $this->array = array_merge($this->array, $array);
        return $this;
    }








    /**
     *  \IteratorAggregate
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->array);
    }

    /**
     * \ArrayAccess
     *
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->array[] = $value;
        } else {
            $this->array[$offset] = $value;
        }
    }

    /**
     * \ArrayAccess
     *
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->array[$offset]);
    }

    /**
     * \ArrayAccess
     *
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->array[$offset]);
    }

    /**
     * \ArrayAccess
     *
     * @param string $offset
     * @return bool
     */
    public function offsetGet($offset)
    {
        return isset($this->array[$offset]) ? $this->array[$offset] : null;
    }

    /**
     * \Serializable
     *
     * @return string
     */
    public function serialize()
    {
        return serialize($this->array);
    }

    /**
     * \Serializable
     *
     * @param string $data
     */
    public function unserialize($data)
    {
        $this->array = unserialize($data);
    }

    /**$name
     * \Countable
     *
     * @return int
     */
    public function count()
    {
        return count($this->array);
    }

    /**
     * Allow for the items to be treated as object params
     * EG: $arrObj->item = $arrObj['item']
     *
     * @param $key
     * @param mixed $val
     * @return ArrayObject
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
     * @return ArrayObject
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
     * @deprecated Use has()
     */
    public function exists($key)
    {
        return $this->has($key);
    }

    /**
     * Test if an array key exists in this object
     *
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Remove an entry from the registry cache
     *
     * @param string $key
     * @return ArrayObject
     * @deprecated Use remove()
     */
    public function delete($key)
    {
        return $this->remove($key);
    }

    /**
     * Remove an entry from the registry cache
     *
     * @param string $key
     * @return ArrayObject
     */
    public function remove($key)
    {
        $this->offsetUnset($key);
        return $this;
    }

    /**
     * toString
     *
     * @return string
     */
    public function toString()
    {
        $str = "";
        $arr = $this->array;
        ksort($arr);
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
     * __toString
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

}