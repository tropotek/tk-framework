<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Db;

/**
 * The base mapper object that controls the mapping of columns to objects
 *
 * @package Tk\Db
 */
abstract class Mapper extends \Tk\Object
{
    const DELETE = 'del';


    /**
     * @var Mapper
     */
    private static $instance = array();

    /**
     * @var \Tk\Model\DataMap
     */
    protected $dataMap = null;

    /**
     * @var string
     */
    protected $table = '';

    /**
     *
     * @var string
     */
    protected $markDeleted = '';

    /**
     * @var Pdo
     */
    protected $db = null;

    /**
     * @var \Tk\ArrayObject
     */
    protected $statements = null;



    /**
     * __construct
     *
     * @param \PDO $db
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->statements = new \Tk\ArrayObject;
        $this->dataMap = $this->makeDataMap();
    }

    /**
     * Get/Create an instance of a data mapper
     *
     * @param string|\Tk\Model $class
     * @return Mapper
     */
    static function get($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }
        if (substr($class, -3) != 'Map') {
            $class .= 'Map';
        }

        if (!class_exists(substr($class, 0, -3))) {
            $anon = substr($class, 0, -3); // Create dynamic model class
            eval("class $anon extends \\Tk\\Db\\Model {}");
        }
        if (!isset(self::$instance[$class])) {
            self::$instance[$class] = new $class(\Tk\Config::getInstance()->getDb());
        }
        return self::$instance[$class];
    }


    /**
     * Get/create the data map object for this mapper.
     *
     * @return DataMap
     */
    abstract protected function makeDataMap();


    /**
     * Get this objects data map
     *
     * @return \Tk\Model\DataMap
     */
    public function getDataMap()
    {
        return $this->dataMap;
    }

    /**
     * The class name this mapper is used for.
     *
     * @return string
     */
    public function getMapperClassName()
    {
        $c = $this->getClassName();
        return preg_replace('/Map$/', '', $c);

    }

    /**
     * Get the table from this object's mapper.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Set the DB table name
     *
     * @param string $tbl
     * @return $this
     */
    public function setTable($tbl)
    {
        $this->table = $tbl;
        return $this;
    }

    /**
     * If set to a collumn nale then only mark the row deleted do not delete
     *
     * @param string $col
     * @return $this
     */
    public function setMarkDeleted($col)
    {
        $this->markDeleted = $col;
        return $this;
    }

    /**
     * Returns the name of the collumn to mark deleted. (update col to 1)
     * returns null if we are to phissically delete the record
     *
     * @return string
     */
    public function getMarkDeleted()
    {
        return $this->markDeleted;
    }


    /**
     * Get the data access object
     *
     * @return \Tk\Db\Pdo
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * Returns the object id if it is greater than 0 or the nextInsertId if it is 0
     *
     * @param \Tk\Db\Model $obj
     * @return int
     */
    public function getVolitileId($obj)
    {
        if ($obj->id == 0) {
            $id = $this->getDb()->getNextInsertId($this->getTable());
        } else {
            $id = $obj->id;
        }
        return $id;
    }

    /**
     * Create a Loader_Collection object with the raw data and dbTool object.
     *
     * @param \PDOStatement $result
     * @param Tool $tool Optional
     * @return ArrayObject
     */
    public function makeCollection($result, $tool = null)
    {
        // Find Query Total
        $query = $this->getDb()->getLastQuery();
        Pdo::$logLastQuery = false;
        $total = $this->getDb()->countQuery($query);
        Pdo::$logLastQuery = true;
        if ($tool) {
            $tool->setTotal($total);
        }
        $arr = new ArrayObject($result->fetchAll(\PDO::FETCH_ASSOC), $this, $tool);
        $arr->setMaxLength($total);
        return $arr;
    }

    /**
     * loadObject
     *
     * @param array $row
     * @return Model
     */
    public function loadObject($row)
    {
        return $this->getDataMap()->loadObject($row);
    }

    /**
     * toArray
     *
     * @param \Tk\Db\Model $obj
     * @return array
     */
    public function toArray($obj)
    {
        return $this->getDataMap()->toArray($obj);
    }

    /**
     * escapeVar
     *
     * @param mixed $str
     * @return mixed
     */
    private function escapeVar($str)
    {
        if (is_string($str) && ($str[0] == '"' || $str[0] == "'")) {
            $str = substr($str, 1, -1);
            $str = $this->getDb()->escapeString($str);
            $str = "'".$str."'";
        }
        return $str;
    }










    /**
     * Return a select list of fields for a sql query
     *
     * @param string $prepend (optional) Default is a null string
     * @return string
     */
    protected function getSelectList($prepend = '')
    {
        $result = '';
        if ($prepend != null && substr($prepend, -1) != '.') {
            $prepend = $prepend . ".";
        }
        /* @var $map Tk_Db_Map_Interface */
        foreach ($this->getDataMap()->getAllProperties() as $map) {
            $nameList = $map->getColumnNames();
            foreach ($nameList as $v) {
                $result .= $prepend . '`' . $v . '`,';
            }
        }
        return substr($result, 0, -1);
    }

    /**
     * Return an update list of fields for a sql query
     *
     * @param mixed $obj
     * @return string
     */
    protected function getUpdateList($obj)
    {
        $result = '';
        /* @var $map Tk_Db_Map_Interface */
        foreach ($this->getDataMap()->getAllProperties() as $map) {
            $valArr = $map->getColumnValue($obj);
            // Use system date if none exists
            if (($map->getPropertyName() == 'created' || $map->getPropertyName() == 'createdDate')) {
                continue;
            }
            if (($map->getPropertyName() == 'modified' || $map->getPropertyName() == 'modifiedDate') && current($valArr) == 'NULL') {
                $result .= '`' . current($map->getColumnNames()) . "` = '" . date('Y-m-d H:i:s') . "',";
                continue;
            }
            foreach ($valArr as $k => $v) {
                $v = $this->escapeVar($v);
                $result .= '`' . $k . "` = " . $v . ",";
            }
        }
        if ($result) {
            $result = substr($result, 0, -1);
        }
        return $result;
    }

    /**
     * Get the insert text for a query
     *
     * @param mixed $obj
     * @return string
     */
    protected function getInsertList($obj)
    {
        $columns = '';
        $values = '';
        /* @var $map \Tk\Model\Map\Iface */
        foreach ($this->getDataMap()->getAllProperties() as $map) {
            if ($map->isIndex()) { continue; }  // Ignore auto index fields \Tk\Model\Map\Interface::setIndex(true)
            $valArr = $map->getColumnValue($obj);
            // Use system date if none exists
            if ( (($map->getPropertyName() == 'created' || $map->getPropertyName() == 'createdDate') && current($valArr) == 'NULL') ||
                 (($map->getPropertyName() == 'modified' || $map->getPropertyName() == 'modifiedDate') && current($valArr) == 'NULL') )
            {
                $columns .= '`' . current($map->getColumnNames()) . '`,';
                $values .= $this->getDb()->quote(date('Y-m-d H:i:s')) . ",";
                continue;
            }
            foreach ($valArr as $k => $v) {
                $v = $this->escapeVar($v);
                $columns .= '`' . $k . '`,';
                $values .= $v . ",";
            }
        }
        $str = '(' . substr($columns, 0, -1) . ') VALUES(' . substr($values, 0, -1) . ')';
        return $str;
    }


    /**
     * Select a record from a database
     *
     * @param int $id
     * @throws Exception
     * @return Model Returns null on error
     */
    public function select($id)
    {
        $idFields = $this->getDataMap()->getIdPropertyList();
        $idField = current($idFields);
        if ($idField == null) {
            throw new Exception('No Primary Id properties set in the data mapper.');
        }
        $query = sprintf('SELECT SQL_CALC_FOUND_ROWS %s FROM `%s` WHERE `%s` = %d LIMIT 1',
            $this->getSelectList(), $this->getTable(),
            current($idField->getColumnNames()), (int)$id);
        $result = $this->getDb()->query($query);
        if ($result->rowCount()) {
            //$obj = $stat->fetchObject(substr(get_class($this), 0, -3));
            $obj = $this->loadObject($result->fetch(\PDO::FETCH_ASSOC));
            return $obj;
        }
    }

    /**
     * prepeared statement
     * Select a record from a database
     *
     * @param int $id
     * @throws Exception
     * @return Model Returns null on error
     */
    public function _select($id)
    {
        $id = (int)$id;
        $stat = $this->statements->get('select');
        if (!$stat) {
            $idFields = $this->getDataMap()->getIdPropertyList();
            $idField = current($idFields);
            if ($idField == null) {
                throw new Exception('No Primary Id prperties set in the data mapper.');
            }
            $query = sprintf('SELECT %s FROM `%s` WHERE `%s` = ? LIMIT 1',
            $this->getSelectList(), $this->getTable(), current($idField->getColumnNames()) );
            $stat = $this->getDb()->prepare($query);
            $this->statements['select'] = $stat;
        }
        $stat->bindParam(1, $id);
        $stat->execute();
        if ($stat->rowCount()) {
            $obj = $this->loadObject($stat->fetch(\PDO::FETCH_ASSOC));
            //$obj = $stat->fetchObject(substr(get_class($this), 0, -3));
            return $obj;
        }
    }





    /**
     * Select a number of elements from a database
     *
     * @param string $where EG: ""column1"=4 AND "column2"=string"
     * @param Tool $tool
     * @return array
     */
    public function selectMany($where = '', $tool = null)
    {
        return $this->selectFrom('', $where, $tool);
    }

    /**
     * Select a number of elements from a database
     *
     * @param string $from
     * @param string $where EG: ""column1"=4 AND "column2"=string"
     * @param Tool $tool
     * @param int|string $prepend Used for table aliases in a query
     * @param bool $isDistinct
     * @param string $groupBy
     * @return Array
     */
    public function selectFrom($from = '', $where = '', $tool = null, $prepend = '', $isDistinct = true, $groupBy = '')
    {
        $prepend = $this->getDb()->escapeString($prepend);
        $isDistinct = $isDistinct === true ? true : false;
        if (!$from) {
            $from = sprintf('`%s`', $this->getTable());
        }
        if ($prepend && substr($prepend, -1) != '.') {
            $prepend = $prepend . ".";
        }

        if ($where) {
            if ($this->getMarkDeleted()) {
                $where = sprintf(' %s`%s` = 0 AND ', $prepend, $this->getMarkDeleted()) . $where;
            }
            $where = 'WHERE ' . $where;
        } else {
            if ($this->getMarkDeleted()) {
                $where = sprintf('WHERE %s`%s` = 0 ', $prepend, $this->getMarkDeleted());
            }
        }

        $toolStr = '';
        if ($tool) {
            $toolStr = $tool->getSql($prepend);
        }

        $distinct = '';
        if ($isDistinct) {
            $distinct = 'DISTINCT';
        }
        if ($groupBy) {
        	$groupBy = 'GROUP BY ' . str_replace(array(';', '-- ', '/*'), ' ', $groupBy);
        }
        $sql = sprintf('SELECT %s %s FROM %s %s %s %s ', $distinct, $this->getSelectList($prepend), $from, $where, $groupBy, $toolStr);
        $result = $this->getDb()->query($sql);
        return $this->makeCollection($result, $tool);
    }


    // The following function are standard SQL calls


    /**
     * A Utility method that checks the id and does and insert
     * or an update  based on the objects current state
     *
     * @param $obj
     * @return int
     */
    public function save($obj)
    {
        if (!$obj->id) {
            return $this->insert($obj);
        }
        return $this->update($obj);
    }

    /**
     * Insert this object into the database.
     * Returns the new insert id for this object.
     *
     * @param Model $obj
     * @return int
     */
    public function insert($obj)
    {
        $query = sprintf('INSERT INTO `%s` %s', $this->getTable(), $this->getInsertList($obj));

        $this->getDb()->query($query);
        $id = $this->getDb()->lastInsertId();
        $obj->id = $id;
        if ($this->getDataMap()->getProperty('orderBy')) {
            $this->updateValue($id , 'orderBy', $id);
            $obj->orderBy = $id;
            $this->update($obj);
        }
        return $id;
    }

    /**
     * Update this object in the database.
     * Returns The number of affected rows.
     *
     * @param Model $obj
     * @return int The number of affected rows
     */
    public function update($obj)
    {
        $where = '';
        /* @var $map \Tk\Model\Map\Iface */
        foreach ($this->getDataMap()->getIdPropertyList() as $map) {
            $arr = $map->getColumnValue($obj);
            foreach ($arr as $k => $v) {
                $v = $this->escapeVar($v);
                $where .= '`' . $k . '` = ' . $v . ' AND ';
            }
        }
        $where = substr($where, 0, -4);
        $query = sprintf('UPDATE `%s` SET %s WHERE %s', $this->getTable(), $this->getUpdateList($obj), $where);
        return $this->getDb()->exec($query);
    }

    /**
     * Update a single value in a single row
     *
     * @param string $id
     * @param string $column
     * @param mixed $value
     * @return int Return the number of rows affected
     */
    public function updateValue($id, $column, $value)
    {
        $where = 'id = ' . enquote($id);
        $query = sprintf("UPDATE `%s` SET `%s` = '%s' WHERE %s", $this->getTable(), $column, $value, $where);
        return $this->getDb()->exec($query);
    }


    /**
     * Test if this object is deleted or not
     * Also good to test if an object by ID exists in the table
     * if an object is marked deleted this will also return true
     *
     * @param Model $obj
     * @return bool
     */
    public function isDeleted($obj)
    {
        if (!$obj->id) {
            return true;
        }
        $where = '';
        /* @var $map \Tk\Db\Map_Interface */
        foreach ($this->getDataMap()->getIdPropertyList() as $map) {
            $arr = $map->getColumnValue($obj);
            foreach ($arr as $k => $v) {
                $v = $this->escapeVar($v);
                $where .= '`' . $k . '` = ' . $v . ' AND ';
            }
        }
        if ($where)
            $where = substr($where, 0, -4);

        $query = sprintf('SELECT * FROM `%s` WHERE %s LIMIT 1', $this->getTable(), $where);
        $result = $this->getDb()->query($query);
        if ($result->rowCount()) {
            $arr = $result->fetch(\PDO::FETCH_ASSOC);
            if ($this->getMarkDeleted()) {
                if ($arr[$this->getMarkDeleted()] == 0) {
                    return false;
                }
            } else {
                return false;
            }
        }
        return true;
    }


    /**
     * Delete this object from the database.
     * Returns The number of affected rows.
     *
     * @param Model $obj
     * @return int
     */
    public function delete($obj)
    {
        $where = '';
        /* @var $map \Tk\Model\Map\Iface */
        foreach ($this->getDataMap()->getIdPropertyList() as $map) {
            $arr = $map->getColumnValue($obj);
            foreach ($arr as $k => $v) {
                $v = $this->escapeVar($v);
                $where .= '`' . $k . '` = ' . $v . ' AND ';
            }
        }
        $where = substr($where, 0, -4);

        if ($this->getMarkDeleted()) {
            $query = sprintf('UPDATE `%s` SET `%s` = 1 WHERE %s LIMIT 1', $this->getTable(), $this->getMarkDeleted(), $where);
        } else {
            $query = sprintf('DELETE FROM `%s` WHERE %s LIMIT 1', $this->getTable(), $where);
        }
        return $this->getDb()->exec($query);
    }

    /**
     * Delete an array of Ids from the database
     *
     * @param array $ids
     * @return int The number of affected rows.
     */
    public function deleteGroup($ids)
    {
        $where = '';
        /* @var $map \Tk\Model\Map\Iface */
        foreach ($ids as $id) {
            $where .= '`id` = ' . (int)$id . ' OR ';
        }
        $where = substr($where, 0, -3);

        if ($this->getMarkDeleted()) {
            $query = sprintf('UPDATE `%s` SET `%s` = 1 WHERE %s', $this->getTable(), $this->getMarkDeleted(), $where);
        } else {
            $query = sprintf('DELETE FROM `%s` WHERE %s', $this->getTable(), $where);
        }
        return $this->getDb()->exec($query);
    }

    /**
     * Count records in a DB
     *
     * @param string $from
     * @param string $where
     * @return int
     */
    public function countFrom($from = '', $where = '')
    {
        if ($from == '') {
            $from = sprintf('`%s`', $this->getTable());
        }
        if ($this->getMarkDeleted()) {
            if ($where) {
                $where .= sprintf(' AND `%s` = 0', $this->getMarkDeleted());
            } else {
                $where .= sprintf('`%s` = 0', $this->getMarkDeleted());
            }
            $sql = sprintf("SELECT COUNT(*) AS i FROM %s WHERE %s", $from, $where);
        } else {
            if (!$where) {
                $where = "1";
            }
            $sql = sprintf("SELECT COUNT(*) AS i FROM %s WHERE %s", $from, $where);
        }

        $result = $this->getDb()->query($sql);
        $value = $result->fetch(\PDO::FETCH_ASSOC);
        return intval($value['i'], 10);
    }

    /**
     * Count records in a DB
     *
     * @param string $where
     * @return int
     */
    public function count($where = '')
    {
        $from = sprintf('`%s`', $this->getTable());
        return $this->countFrom($from, $where);
    }

    /**
     * Find an object by its id
     *
     * @param int $id
     * @return Model
     */
    public function find($id)
    {
        return $this->select($id);
    }

    /**
     * Find all object within the DB tool's parameters
     *
     * @param Tool $tool
     * @return ArrayObject
     */
    public function findAll($tool = null)
    {
        return $this->selectMany('', $tool);
    }

    /**
     * Find first record by created
     *
     * @return Model
     */
    public function findFirst()
    {
        return $this->selectMany('', Tool::create('`created`', 1))->fetch();
    }

    /**
     * Find last object by created
     *
     * @return Model
     */
    public function findLast()
    {
        return $this->selectMany('', Tool::create('`created` DESC', 1))->fetch();
    }



    // Only use the following methods if the field "orderBy" exists in teh table.

    /**
     * Swap the order of 2 records
     *
     * @param Model $fromObj
     * @param Model $toObj
     * @return int
     */
    public function orderSwap($fromObj, $toObj)
    {
        if (!$this->getDataMap()->getProperty('orderBy')) {
            return 0;
        }
        $query = sprintf("UPDATE `%s` SET `orderBy` = '%s' WHERE `id` = %d", $this->getTable(), (int)$toObj->orderBy, (int)$fromObj->id);
        $this->getDb()->exec($query);
        $query = sprintf("UPDATE `%s` SET `orderBy` = '%s' WHERE `id` = %d", $this->getTable(), (int)$fromObj->orderBy, (int)$toObj->id);
        $this->getDb()->exec($query);
    }

    /**
     * Reset the order values to id values.
     *
     */
    public function resetOrder()
    {
        if (!$this->getDataMap()->getProperty('orderBy')) {
            return 0;
        }
        $query = sprintf('UPDATE `%s` SET `orderBy` = `id`', $this->getTable());
        return $this->getDb()->exec($query);
    }



}
