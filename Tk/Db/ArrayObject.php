<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Db;
use Tk\Exception;

/**
 * This is the loader interface and should be used on the object or its loader
 * to generate a datamap, other functions can be created to construct different maps
 * however there must at least be one data map function available
 *
 * getDataMap() can and will be called by default for all objects using the data loader,
 * if no dataMap is supplied.
 *
 * @package Tk\Db
 */
class ArrayObject extends \Tk\Object implements \Iterator, \Countable, \ArrayAccess
{

    /**
     * @var \Tk\Db\Mapper
     */
    protected $mapper = null;

    /**
     * The raw database rows as associative arrays.
     * @var array
     */
    protected $result = null;

    /**
     * @var int
     */
    protected $idx = 0;

    /**
     * If this value is set this is the max results
     * from a query without any limit set..
     * @var int
     */
    protected $maxLength = 0;

    /**
     * This may or may not exist depending on the source of the array data
     * @var \Tk\Db\Tool
     */
    protected $tool = null;



    /**
     * Create a collection object
     *
     * @param $result
     * @param \Tk\Db\Mapper $mapper
     * @param \Tk\Db\Tool $tool
     */
    public function __construct($result, $mapper = null, $tool = null)
    {
        $this->result = $result;
        $this->mapper = $mapper;
        if (!$tool) {
            $tool = new Tool();
            $tool->setTotal($result);
        }
        $this->tool = $tool;
    }

    /**
     * Destructor
     *
     */
    public function __destruct()
    {
        $this->result = null;
        $this->tool = null;
    }


    /**
     * This is the number of records without any limits set
     * This value can be handy for pagination and totals calcs.
     *
     * @param int $i
     * @return \Tk\Db\ArrayObject
     */
    public function setMaxLength($i)
    {
        $this->maxLength = $i;
        return $this;
    }

    /**
     * This is the number of records without any limits set
     * This value can be handy for pagination and totals calcs.
     *
     * @return int
     */
    public function getMaxLength()
    {
        if ($this->tool) {
            return $this->tool->getTotal();
        }
        return $this->maxLength;
    }

    /**
     * Return the tool object associated to this result set.
     * May not exist.
     *
     * @return \Tk\Db\Tool
     */
    public function getTool()
    {
        return $this->tool;
    }

    /**
     * Return the tool object associated to this result set.
     * May not exist.
     *
     * @return \Tk\Db\Mapper
     */
    public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * Return a standard PHP array of the objects
     *
     * @return \PDOStatement
     */
    public function getResult()
    {
        return $this->result;
    }


    /**
     * Get a mapped object
     *
     * @param int $i
     * @return \stdClass
     */
    public function get($i = 0)
    {
        if (isset($this->result[$i])) {
            if ($this->mapper) {
                return $this->mapper->loadObject($this->result[$i]);
            } else if (is_array($this->result[$i])) {
                return (object)$this->result[$i];
            } else {
                return $this->result[$i];
            }
        }
    }



    //   Iterator Interface

    /**
     * rewind
     *
     * @return \Tk\Db\ArrayObject
     */
    public function rewind()
    {
        $this->idx = 0;
        return $this;
    }

    /**
     * Return the element at the current index
     *
     * @return \Tk\Object
     */
    public function current()
    {
        return $this->get($this->idx);
    }

    /**
     * get the key value
     *
     * @return string
     */
    public function key()
    {
        return $this->idx;
    }

    /**
     * Increment the counter
     *
     * @return \Tk\Object
     */
    public function next()
    {
        $this->idx++;
    }

    /**
     * Valid
     *
     * @return bool
     */
    public function valid()
    {
        if($this->idx < $this->count()) {
            return true;
        }
        return false;
    }

    //   Countable Interface

    /**
     * Count
     *
     * @return int
     */
    public function count()
    {
        return count($this->result);
    }




    // ArrayAccess Interface

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return bool true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return isset($this->result[$offset]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @throws \Tk\Exception
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if ($this->mapper && get_class($value) != $this->mapper->getMapperClassName()) {
            throw new Exception('Invalid instance type.');
        }
        $value = $this->mapper->toArray($value);
        if ($offset) {
            $this->result[$offset] = $value;
        } else {
            $this->result[] = $value;
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        // Do nothing
    }








    /**
     * Convert this array object to a PHP standard array
     *
     * @return array
     */
    public function toArray()
    {
        $arr = array();
        foreach ($this as $obj) {
            $arr[] = $obj;
        }
        return $arr;
    }

    /**
     * Convert this object to a PHP standard array
     * containing the object ID's only not the entire object.
     *
     * @return array
     */
    public function toIdArray()
    {
        $arr = array();
        foreach ($this as $obj) {
            if (array_key_exists('id', get_object_vars($obj))) {
                $arr[] = $obj->id;
            }
        }
        return $arr;
    }

}
