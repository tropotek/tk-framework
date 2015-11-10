<?php
namespace Tk\Db;

/**
 * Class Mapper
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
abstract class Mapper
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
     * Override this method in your own mapper
     *
     * serialize object for saving into DB via an array
     *
     * output array (
     *   'tblColumn' => 'columnValue'
     * )
     *
     * @param Model|\stdClass $obj
     * @return array
     */
    public function dbSerialize($obj)
    {
        return (array)$obj;
    }

    /**
     * Override this method in your own mapper
     *
     * Unserialize Db row data into the required object
     *
     * input array (
     *   'tblColumn' => 'columnValue'
     * )
     *
     * @param Model|\stdClass|array $row
     * @return Model|\stdClass
     */
    public function dbUnserialize($row)
    {
        return $row;
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
     *
     *
     * @return Model
     */
    public function loadObject($array)
    {
        return $this->dbUnserialize($array);
    }

    /**
     * Insert
     *
     * @param mixed $obj
     * @return int Returns the new insert id
     */
    public function insert($obj)
    {
        $pk = $this->getPrimaryKey();
        $bind = $this->dbSerialize($obj);

        $cols = implode(", ", $this->backtickArray(array_keys($bind)));
        $values = implode(", :", array_keys($bind));
        foreach ($bind as $col => $value) {
            if ($col == $pk) continue;
            if ($col == 'modified' || $col == 'created') {
                $value = date('Y-m-d H:i:s');
            }
            unset($bind[$col]);
            $bind[":" . $col] = $value;
        }
        $sql = "INSERT INTO " . $this->table . " (" . $cols . ")  VALUES (:" . $values . ")";
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
        $bind = $this->dbSerialize($obj);
        $set = array();
        foreach ($bind as $col => $value) {
            if ($col == 'modified') {
                $value = date('Y-m-d H:i:s');
            }
            unset($bind[$col]);
            $bind[":" . $col] = $value;
            $set[] = '`'.$col . '` = :' . $col;
        }
        $where = '`'.$pk . '` = ' . $bind[':'.$pk];
        $sql = "UPDATE `" . $this->table . "` SET " . implode(", ", $set) . (($where) ? " WHERE " . $where : " ");

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
        $where = $pk . ' = ' . $obj->$pk;
        //$where = $pk . ' = ' . $obj->getId();
        $sql = 'DELETE FROM `' . $this->table .'` ' . (($where) ? ' WHERE ' . $where : ' ');
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     *
     * $options:
     *   array (
     *     'orderBy' => '',
     *     'limit' => '',
     *     'offset' => '',
     *
     *     'boolOperator' => 'AND',
     *     'groupBy' => '',
     *     'having' => ''
     *   );
     *
     *
     * @param array $bind
     * @param Tool $tool
     * @param string $boolOperator
     * @return ArrayObject
     * @see http://www.sitepoint.com/integrating-the-data-mappers/
     */
    public function select($bind = array(), $tool = null, $boolOperator = 'AND')
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
                $where[] = $alias.'`'.$col . '` = :' . $col;
            }
        }
        $where = implode(' ' . $boolOperator . ' ', $where);

        // Build Query
        $sql = sprintf('SELECT SQL_CALC_FOUND_ROWS %s * FROM %s %s ',
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
    public function selectMany($where = '', $tool = null)
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
            $from = sprintf('`%s` %s', $this->getTable(), $this->getAlias());
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

        $sql = sprintf('SELECT SQL_CALC_FOUND_ROWS %s %s* FROM %s %s %s ', $distinct, $alias, $from, $where, $toolStr);

        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute();

        $arr = ArrayObject::createFromMapper($this, $stmt, $tool);
        return $arr;
    }

    /**
     * Call this directly after yor select query to get the total available rows
     *
     * @return int
     */
    public function getFoundRows()
    {
        $sql = 'SELECT FOUND_ROWS()';
        $r = $this->getDb()->query($sql);
        return (int)$r->fetch(PDO::FETCH_COLUMN);
    }

    /**
     *
     * @param $id
     * @return Model|null
     */
    public function find($id)
    {
        $bind = array(
            $this->getPrimaryKey() => $id
        );
        $list = $this->select($bind, \Tk\Db\Tool::create('', 1));
        return $list->current();
    }

    /**
     * Find all objects in DB
     *
     * @param Tool $tool
     * @return array
     */
    public function findAll($tool = null)
    {
        return $this->selectMany('', $tool);
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

    /**
     * @param $array
     * @return mixed
     */
    private function backtickArray($array)
    {
        foreach($array as $k => $v) {
            $array[$k] = '`'.trim($array[$k], '`').'`';
        }
        return $array;
    }


}