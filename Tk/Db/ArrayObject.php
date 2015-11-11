<?php
namespace Tk\Db;

use Tk\Db\Exception;

/**
 * This objected is essentially a wrapper around the PdoStatement object with added features
 * such as holding the Model Mapper, and Db\Tool objects.
 *
 * It automatially maps an obects data if the Model has the magic methods available
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class ArrayObject implements \Iterator, \Countable
{

    /**
     * @var Mapper
     */
    protected $mapper = null;

    /**
     * The raw database rows as associative arrays.
     * @var PdoStatement
     */
    protected $statement = null;

    /**
     * The raw database rows as associative arrays.
     * @var array
     */
    protected $rows = null;

    /**
     * @var int
     */
    protected $idx = 0;

    /**
     * The total number of rows found without LIMIT clause
     * @var int
     */
    protected $foundRows = 0;

    /**
     * This may or may not exist depending on the source of the array data
     * @var Tool
     */
    protected $tool = null;



    /**
     * Create a DB array list object
     *
     * @param array $rows
     */
    public function __construct($rows)
    {
        $this->rows = $rows;
    }

    /**
     * @param Mapper $mapper
     * @param PdoStatement $statement
     * @param Tool $tool
     * @return ArrayObject
     */
    static function createFromMapper(Mapper $mapper, PdoStatement $statement, $tool = null)
    {

        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $obj = new self($rows);
        $obj->foundRows = $mapper->getFoundRows();
        $obj->mapper = $mapper;
        $obj->statement = $statement;
        if (!$tool) {
            $tool = new Tool();
        }
        $obj->tool = $tool;
        return $obj;
    }

    /**
     * Destructor
     *
     */
    public function __destruct()
    {
        $this->statement = null;
        $this->tool = null;
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
     * @return Mapper
     */
    public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * s
     * @return PdoStatement
     */
    public function getStatement()
    {
        return $this->statement;
    }


    /**
     * Get the result rows as a standard array.
     * @return array
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @param int $i
     * @return Model|array|null
     */
    public function get($i)
    {
        if (isset($this->rows[$i])) {
            if ($this->mapper) {
                //return $this->mapper->loadObject($this->rows[$i]);
                return $this->mapper->map($this->rows[$i]);
            }
            return (object)$this->rows[$i];
        }
    }

    /**
     * Get the total rows available count.
     *
     * This value will be the available count without a limit.
     *
     * @return int
     */
    public function getFoundRows()
    {
        return $this->foundRows;
    }


    //   Iterator Interface

    /**
     * rewind
     *
     * @return $this
     */
    public function rewind()
    {
        $this->idx = 0;
        return $this;
    }

    /**
     * Return the element at the current index
     *
     * @return Model
     */
    public function current()
    {
        return $this->get($this->idx);
    }

    /**
     * Increment the counter
     *
     * @return Model
     */
    public function next()
    {
        $this->idx++;
        return $this->current();
    }

    /**
     * get the key value
     *
     * @return int
     */
    public function key()
    {
        return $this->idx;
    }

    /**
     * Valid
     *
     * @return bool
     */
    public function valid()
    {
        if ($this->current()) {
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
        return count($this->rows);
    }


}
