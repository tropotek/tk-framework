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
     * Get/Create an instance of a data mapper.
     *
     * @param string $mapperClass The Model mapper class string EG: 'App\Db\UserMap'
     * @return Mapper
     */
    static function create($mapperClass, $modelClass = '')
    {
        if (!isset(self::$instance[$mapperClass])) {
            self::$instance[$mapperClass] = new $mapperClass();
            self::$instance[$mapperClass]->modelClass = $modelClass;
        }
        return self::$instance[$mapperClass];
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
     * @return string
     */
    public function getModelClass()
    {
        return $this->modelClass;
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

    /**
     * Insert
     *
     * @param mixed $obj
     * @return int Returns the new insert id
     */
    public function insert($obj)
    {
        $bind = $this->dbSerialize($obj);

        $cols = implode(", ", $this->backtickArray(array_keys($bind)));
        $values = implode(", :", array_keys($bind));
        foreach ($bind as $col => $value) {
            if ($col == $this->primaryKey) continue;
            if ($col == 'modified' || $col == 'created') {
                $value = date('Y-m-d H:i:s');
            }
            // TODO add object toString helpers/hooks here
            if ($value instanceof \DateTime) {
                $value = $value->format('Y-m-d H:i:s');
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
        $bind = $this->dbSerialize($obj);
        $set = array();
        foreach ($bind as $col => $value) {
            if ($col == 'modified') {
                $value = date('Y-m-d H:i:s');
            }
            // TODO add object toString helpers/hooks here
            if ($value instanceof \DateTime) {
                $value = $value->format('Y-m-d H:i:s');
            }
            unset($bind[$col]);
            $bind[":" . $col] = $value;
            $set[] = '`'.$col . '` = :' . $col;
        }
        $where = '`'.$this->primaryKey . '` = ' . $bind[':'.$this->primaryKey];
        $sql = "UPDATE `" . $this->table . "` SET " . implode(", ", $set) . (($where) ? " WHERE " . $where : " ");

        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute($bind);
        return $stmt->rowCount();

    }

    /**
     * Save the object, let the code decide weather to insert ot update the db.
     *
     * This assumes the object primary key is `id` and exists in the object to be saved.
     *
     * @param $obj
     * @throws \Exception
     */
    public function save($obj)
    {
        if (!property_exists($obj, $this->primaryKey)) {
            throw new \Exception('No valid primary key found');
        }
        if ($obj->id == 0) {
            $this->insert($obj);
        } else {
            $this->update($obj);
        }
    }

    /**
     * Delete object
     *
     * @param $obj
     * @return int
     */
    public function delete($obj)
    {
        $where = $this->primaryKey . ' = ' . $obj->{$this->primaryKey};
        $sql = 'DELETE FROM `' . $this->table .'` ' . (($where) ? ' WHERE ' . $where : ' ');
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute();
        return $stmt->rowCount();
    }


    /**
     *
     * @param $id
     * @return Model|null
     */
    public function find($id)
    {
        $bind = array(
            $this->primaryKey => $id
        );
        $list = $this->select($bind, array(\Tk\Db\Pdo::PARAM_LIMIT => 1));
        return current($list);
    }

    /**
     * Find all objects in DB
     *
     * @param array $params
     * @return array
     */
    public function findAll($params = array())
    {
        return $this->select(array(), $params);
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
     * @param array  $bind
     * @param array $params
     * @return array
     * @see http://www.sitepoint.com/integrating-the-data-mappers/
     */
    public function select(array $bind = array(), $params = array())
    {
        $params = array_merge(array('boolOperator' => 'AND'), $params);
        $where = array();
        if ($bind) {
            foreach ($bind as $col => $value) {
                unset($bind[$col]);
                $bind[":" . $col] = $value;
                $where[] = '`'.$col . "` = :" . $col;
            }
        }
        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM " . $this->table . (($bind) ? " WHERE " . implode(" " . $params['boolOperator'] . " ", $where) : " ");

        if (!empty($params[\Tk\Db\Pdo::PARAM_GROUP_BY])) {
            $sql .= ' GROUP BY ' . $params[\Tk\Db\Pdo::PARAM_GROUP_BY];
        }

        if (!empty($params[\Tk\Db\Pdo::PARAM_HAVING])) {
            $sql .= ' HAVING ' . $params[\Tk\Db\Pdo::PARAM_HAVING];
        }

        if (!empty($params[\Tk\Db\Pdo::PARAM_ORDER_BY])) {
            $sql .= ' ORDER BY ' . $params[\Tk\Db\Pdo::PARAM_ORDER_BY];
        }

        if (!empty($params[\Tk\Db\Pdo::PARAM_LIMIT])) {
            $sql .= ' LIMIT ' . (int)$params[\Tk\Db\Pdo::PARAM_LIMIT];
        }
        if (!empty($params[\Tk\Db\Pdo::PARAM_OFFSET])) {
            $sql .= ' OFFSET ' . (int)$params[\Tk\Db\Pdo::PARAM_OFFSET];
        }


        $stmt = $this->getDb()->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_CLASS, $this->getModelClass());     // to populate before the constructor is called.
        //$stmt->setFetchMode(\PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, $this->getModelClass());     // To populate after the constructor is called
        $stmt->execute($bind);

        $stmt->setParams($params);
        $stmt->addParam(\Tk\Db\Pdo::PARAM_FOUND_ROWS, $this->getFoundRows());
        $stmt->addParam('mapper', $this);

        return $stmt;

//        $list = array();
//        foreach($stmt as $row) {
//            $list[] = $this->dbUnserialize((array)$row);
//        }
//        return $list;
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
     * Return a string for the SQL query
     *
     * ORDER BY `cell`
     * LIMIT 10 OFFSET 30
     *
     * @param string $prepend  If set will be prepended to any fields without a prefix string.
     * @param Params $dbParams
     * @return string
     */
    public function getSqlFromParams(Params $dbParams, $prepend = '')
    {
        $orderBy = '';
        if ($dbParams->getOrderBy()) {
            $orFields = str_replace(array(';', '-- ', '/*'), ' ', $dbParams->getOrderBy());
            if ($prepend) {
                if ($prepend && substr($prepend, -1) != '.') {
                    $prepend = $prepend . ".";
                }
                $arr = explode(',', $orFields);
                foreach ($arr as $i => $str) {
                    $str = trim($str);
                    if (preg_match('/^(ASC|DESC|FIELD\(|RAND\(|IF\(|NULL)/i', $str)) continue;
                    if (!preg_match('/^([a-z]+\.)?`/i', $str)) continue;
                    //if (!preg_match('/^([a-z]+\.)?`/i', $str)) continue;
                    if (!preg_match('/^([a-zA-Z0-9_-]+\.)/', $str) && is_string($str)) {
                        $str = $prepend . $str;
                    }
                    $arr[$i] = $str;
                }
                $orFields = implode(', ', $arr);
            }

            $orderBy = 'ORDER BY ' . $orFields;
        }
        $limitStr = '';
        if ($dbParams->getLimit() > 0) {
            $limitStr = 'LIMIT ' . (int)$dbParams->getLimit();
            if ($dbParams->getOffset()) {
                $limitStr .= ' OFFSET ' . (int)$dbParams->getOffset();
            }
        }
        return $orderBy . ' ' . $limitStr;
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