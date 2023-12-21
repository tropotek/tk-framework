<?php
namespace Tk\Db\Mapper;

use Tk\Db\PdoStatement;
use Tk\Db\Tool;

/**
 * This objected is essentially a wrapper around the PdoStatement object with added features
 * such as holding the Model Mapper, and Tool objects.
 *
 * It automatically maps an objects data if the Model has the magic methods available
 *
 *
 * TODO: One day PDO may be able to do this serially, this is a big memory hog...
 *        Currently we cannot subclass the PDOStatement::fetch...() methods correctly [php: 5.6.27]
 *
 * NOTE: For large datasets that could fill the memory, this object should not be used
 *        instead get statement and manually iterate the data.
 */
class Result implements \Iterator, \Countable
{

    protected ?Mapper $mapper = null;

    protected ?PdoStatement $statement = null;

    protected ?Tool $tool = null;

    protected ?array $rows = null;

    protected int $idx = 0;

    /**
     * The total number of rows found without LIMIT clause
     */
    protected int $foundRows = 0;


    public function __construct(array $rows)
    {
        $this->rows = $rows;
        $this->foundRows = count($rows);
    }

    /**
     * Create an array object that uses the DB mappers to load the object
     */
    static function createFromMapper(Mapper $mapper, PdoStatement $statement, ?Tool $tool = null): Result
    {
        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $obj = new self($rows);
        $obj->mapper = $mapper;
        $obj->statement = $statement;
        $tool = $tool ?? new Tool();
        $obj->tool = $tool;
        [$_limit, $_offset, $total] = $mapper->getDb()->countFoundRows($statement->queryString, $statement->getBindParams());
        $tool->setFoundRows($total);
        $obj->foundRows = $tool->getFoundRows();
        return $obj;
    }

    /**
     * Create an array object from an SQL statement when no mappers and objects area used
     */
    static function create(PdoStatement $statement, ?Tool $tool = null, ?int $foundRows = null): Result
    {
        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $obj = new self($rows);
        if ($foundRows === null) {
            if ($tool && $tool->getFoundRows()) {
                $foundRows = $tool->getFoundRows();
            } else {
                $foundRows = count($rows);
                if (method_exists($statement, 'getPdo') && $statement->getPdo())
                    $foundRows = $statement->getPdo()->countFoundRows($statement->queryString);
            }
        }
        $obj->statement = $statement;
        $tool = $tool ?? new Tool();
        $obj->tool = $tool;
        $tool->setFoundRows($foundRows);
        $obj->foundRows = $tool->getFoundRows();
        return $obj;
    }

    public function __destruct()
    {
        $this->statement = null;
        $this->tool = null;
    }

    /**
     * Return the tool object associated to this result set.
     * May not exist.
     */
    public function getTool(): Tool
    {
        return $this->tool;
    }

    /**
     * Return the tool object associated to this result set.
     * May not exist.
     */
    public function getMapper(): ?Mapper
    {
        return $this->mapper;
    }

    public function getStatement(): ?PdoStatement
    {
        return $this->statement;
    }

    /**
     * Get the result rows as a standard array.
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    public function get(int $i): mixed
    {
        if (isset($this->rows[$i])) {
            if ($this->getMapper()) {
                $class = $this->getMapper()->getModelClass();
                $obj = new $class();
                $this->mapper->getDbMap()->loadObject($obj, $this->rows[$i]);
                return $obj;
            }
            return (object)$this->rows[$i];
        }
        return null;
    }

    /**
     * Return the total number of rows found.
     * When using SQL it would be the query with no limit...
     */
    public function countAll(): int
    {
        return $this->foundRows;
    }

    //   Countable Interface

    /**
     * Count the number of records returned from the SQL query
     */
    public function count(): int
    {
        return count($this->rows);
    }


    //   Iterator Interface

    /**
     * rewind
     */
    public function rewind(): void
    {
        $this->idx = 0;
    }

    /**
     * Return the element at the current index
     */
    public function current(): mixed
    {
        return $this->get($this->idx);
    }

    /**
     * Increment the counter
     */
    public function next(): void
    {
        $this->idx++;
    }

    /**
     * get the key value
     */
    public function key(): int
    {
        return $this->idx;
    }

    /**
     * Valid
     */
    public function valid(): bool
    {
        if ($this->current()) {
            return true;
        }
        return false;
    }

    /**
     * If the keyField and-or value field are set then this will
     * return the array with a key and the required value.
     */
    public function toArray(?string $valueField = null, ?string $keyField = null): array
    {
        $arr = [];
        foreach($this as $k => $obj) {
            $v = $obj;
            if ($valueField && array_key_exists($valueField, get_object_vars($obj))) {
                $v = $obj->$valueField;
            }
            if ($keyField && array_key_exists($keyField, get_object_vars($obj))) {
                $k = $obj->$keyField;
            }
            $arr[$k] = $v;
        }
        return $arr;
    }

}
