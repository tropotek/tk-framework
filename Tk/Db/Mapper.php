<?php
namespace Tk\Db;

/**
 * Class Mapper
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
abstract class Mapper implements Mappable
{

    const PARAM_GROUP_BY = 'groupBy';
    const PARAM_HAVING = 'having';
    const PARAM_ORDER_BY = 'orderBy';
    const PARAM_LIMIT = 'limit';
    const PARAM_OFFSET = 'offset';
    const PARAM_DISTINCT = 'distinct';
    const PARAM_FOUND_ROWS = 'foundRows';
    
    /**
     * @var Mapper[]
     */
    private static $instance = array();

    /**
     * @var string
     */
    protected $table = '';

    /**
     * @var string
     */
    protected $modelClass = '';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var Pdo
     */
    protected $db = null;

    /**
     * @var string
     */
    protected $alias = 'a';



    /**
     * Get/Create an instance of a data mapper.
     *
     * @param string $mapperClass The Model mapper class string EG: 'App\Db\UserMap'
     * @return Mapper
     */
    static function create($mapperClass, $modelClass = '')
    {
        if (!isset(self::$instance[$mapperClass])) {
            self::$instance[$mapperClass] = new $mapperClass();
            //self::$instance[$mapperClass]->modelClass = $modelClass;
        }
        return static::$instance[$mapperClass];
    }

    /**
     * Map the data from a DB row to the required object
     *
     * Input: array (
     *   'tblColumn' => 'columnValue'
     * )
     *
     * Output: Should return an \stdClass or \Tk\Model object
     *
     * @param Model|\stdClass|array $row
     * @return Model|\stdClass
     * @since 2.0.0
     */
    public function map($row)
    {
        return (object)$row;
    }

    /**
     * Un-map an object to an array ready for DB insertion.
     * All filds and types must match the required DB types.
     *
     * Input: This requires a \Tk\Model or \stdClass object as input
     *
     * Output: array (
     *   'tblColumn' => 'columnValue'
     * )
     *
     * @param Model|\stdClass $obj
     * @return array
     * @since 2.0.0
     */
    public function unmap($obj)
    {
        return (array)$obj;
    }

    /**
     * Insert
     *
     * @param mixed $obj
     * @return int Returns the new insert id
     */
    public function insert($obj)
    {
        $pk = $this->getDb()->quoteParameter($this->getPrimaryKey());
        $bind = $this->unmap($obj);

        $cols = implode(', ', Pdo::quoteParameterArray(array_keys($bind)));
        $values = implode(', :', array_keys($bind));
        foreach ($bind as $col => $value) {
            if ($col == $pk) continue;
            if ($col == 'modified' || $col == 'created') {
                $value = date('Y-m-d H:i:s');
            }
            unset($bind[$col]);
            $bind[':' . $col] = $value;
        }
        $sql = 'INSERT INTO ' . $this->getDb()->quoteParameter($this->table) . ' (' . $cols . ')  VALUES (:' . $values . ')';
        $this->getDb()->prepare($sql)->execute($bind);
        $id = (int)$this->getDb()->lastInsertId();
        return $id;
    }

    /**
     *
     * @param $obj
     * @return int
     */
    public function update($obj)
    {
        $pk = $this->getPrimaryKey();
        $bind = $this->unmap($obj);
        $set = array();
        foreach ($bind as $col => $value) {
            if ($col == 'modified') {
                $value = date('Y-m-d H:i:s');
            }
            unset($bind[$col]);
            $bind[':' . $col] = $value;
            $set[] = $this->getDb()->quoteParameter($col) . ' = :' . $col;
        }
        $where = $this->getDb()->quoteParameter($pk) . ' = ' . $bind[':'.$pk];
        $sql = 'UPDATE ' . $this->getDb()->quoteParameter($this->table) . ' SET ' . implode(', ', $set) . (($where) ? ' WHERE ' . $where : ' ');

        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute($bind);
        return $stmt->rowCount();

    }

    /**
     * Save the object, let the code decide weather to insert ot update the db.
     *
     *
     * @param Model $obj
     * @throws \Exception
     */
    public function save($obj)
    {
        $pk = $this->getPrimaryKey();
        if (!property_exists($obj, $pk)) {
            throw new \Exception('No valid primary key found');
        }
        if ($obj->$pk == 0) {
            $this->insert($obj);
        } else {
            $this->update($obj);
        }
    }

    /**
     * Delete object
     *
     * @param Model $obj
     * @return int
     */
    public function delete($obj)
    {
        $pk = $this->getPrimaryKey();
        $where = $this->getDb()->quoteParameter($pk) . ' = ' . $obj->$pk;
        $sql = 'DELETE FROM ' . $this->getDb()->quoteParameter($this->table) .' ' . (($where) ? ' WHERE ' . $where : ' ');
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * A select query using a prepared statement. Less control
     *
     *
     * @param array $bind
     * @param Tool $tool
     * @param string $boolOperator
     * @return ArrayObject
     * @see http://www.sitepoint.com/integrating-the-data-mappers/
     * @deprecated See if we need this ?
     */
    public function selectPrepared($bind = array(), $tool = null, $boolOperator = 'AND')
    {
        if (!$tool instanceof Tool) {
            $tool = new Tool();
        }

        $alias = $this->getAlias();
        if ($alias) {
            $alias = $alias . '.';
        }

        $from = $this->getTable() . ' ' . $this->getAlias();
        $where = array();
        if ($bind) {
            foreach ($bind as $col => $value) {
                unset($bind[$col]);
                $bind[':' . $col] = $value;
                $where[] = $alias. $this->getDb()->quoteParameter($col) . ' = :' . $col;
            }
        }
        $where = implode(' ' . $boolOperator . ' ', $where);

        // Build Query
        $foundRowsKey = '';
        if ($this->getDb()->getDriver() == 'mysql') {
            $foundRowsKey = 'SQL_CALC_FOUND_ROWS';
        }
        $sql = sprintf('SELECT %s %s * FROM %s %s ',
            $foundRowsKey,
            $tool->isDistinct() ? 'DISTINCT' : '',
            $from,
            ($bind) ? ' WHERE ' . $where : ' '
        );
        $sql .= $tool->toSql();

        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute($bind);

        $arr = ArrayObject::createFromMapper($this, $stmt, $tool);
        return $arr;
    }

    /**
     * Select a number of elements from a database
     *
     * @param string $where EG: "`column1`=4 AND `column2`='string'"
     * @param Tool $tool
     * @return ArrayObject
     */
    public function select($where = '', $tool = null)
    {
        return $this->selectFrom('', $where, $tool);
    }

    /**
     * Select a number of elements from a database
     *
     * @param string $from
     * @param string $where EG: "`column1`=4 AND `column2`='string'"
     * @param Tool $tool
     * @return ArrayObject
     */
    public function selectFrom($from = '', $where = '', $tool = null)
    {
        if (!$tool instanceof Tool) {
            $tool = new Tool();
        }

        $alias = $this->getAlias();
        if ($alias) {
            $alias = $alias . '.';
        }

        if (!$from) {
            $from = sprintf('%s %s', $this->getDb()->quoteParameter($this->getTable()), $this->getAlias());
        }

//        if ($where) {
//            if ($this->getMarkDeleted() && strstr($where, '`'.$this->markDeleted.'`') === false) {
//                $where = sprintf(' %s`%s` = 0 AND ', $alias, $this->getMarkDeleted()) . $where;
//            }
//            $where = 'WHERE ' . $where;
//        } else {
//            if ($this->getMarkDeleted() && strstr($where, '`'.$this->markDeleted.'`') === false) {
//                $where = sprintf('WHERE %s`%s` = 0 ', $alias, $this->getMarkDeleted());
//            }
//        }

        if ($where) {
            $where = 'WHERE ' . $where;
        }

        $distinct = '';
        if ($tool->isDistinct()) {
            $distinct = 'DISTINCT';
        }

        // OrderBy, GroupBy, Limit, etc
        $toolStr = '';
        if ($tool) {
            $toolStr = $tool->toSql($alias);
        }
        $foundRowsKey = '';
        if ($this->getDb()->getDriver() == 'mysql') {
            $foundRowsKey = 'SQL_CALC_FOUND_ROWS';
        }

        $sql = sprintf('SELECT %s %s %s* FROM %s %s %s ', $foundRowsKey, $distinct, $alias, $from, $where, $toolStr);

        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute();

        $arr = ArrayObject::createFromMapper($this, $stmt, $tool);
        return $arr;
    }

    /**
     *
     * @param $id
     * @return Model|null
     */
    public function find($id)
    {
        $where = sprintf('%s = %s', $this->getDb()->quoteParameter($this->getPrimaryKey()), (int)$id);
        $list = $this->select($where);
        return $list->current();
    }

    /**
     * Find all objects in DB
     *
     * @param Tool $tool
     * @return ArrayObject
     */
    public function findAll($tool = null)
    {
        return $this->select('', $tool);
    }


    
    /**
     * Get the table alias used for multiple table queries.
     * The default alias is 'a'
     *
     *   EG: a.`id`
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Set the table alias
     *
     * @param string $alias
     * @return $this
     * @throws Exception
     */
    public function setAlias($alias)
    {
        $alias = trim($alias, '.');
        if (!preg_match('/[a-z0-9_]+/i', $alias))
            throw new Exception('Invalid Table alias value');
        $this->alias = $alias;
        return $this;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param string $table
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * @param string $primaryKey
     */
    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;
    }

    /**
     * @return Pdo
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @param Pdo $db
     */
    public function setDb($db)
    {
        $this->db = $db;
    }

}