<?php
namespace Tk\Db;


/**
 * Class Tool
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
class Tool
{
    use \Tk\InstanceTrait;

    /**
     * Limit the number of records retrieved.
     * If > 0 then mapper should query for the total number of records
     *
     * @var int
     */
    protected $limit = 0;

    /**
     * The record to start retrieval from
     *
     * @var int
     */
    protected $offset = 0;

    /**
     * @var string
     */
    protected $orderBy = 'id DESC';

    /**
     * @var string
     */
    protected $groupBy = '';

    /**
     * @var string
     */
    protected $having = '';

    /**
     * @var bool
     */
    protected $distinct = true;


    /**
     * __construct
     *
     * @param string $orderBy
     * @param int $limit
     * @param int $offset
     * @param string $groupBy
     * @param string $having
     */
    public function __construct($orderBy = '', $limit = 0, $offset = 0, $groupBy = '', $having = '')
    {
        $this->setOrderBy($orderBy);
        $this->setLimit($limit);
        $this->setOffset($offset);
        $this->setGroupBy($groupBy);
        $this->setHaving($having);
    }

    /**
     * Create a listParams object from a request object
     *
     * @param string $orderBy
     * @param int $limit
     * @param int $offset
     * @param string $groupBy
     * @param string $having
     * @return Tool
     */
    static function create($orderBy = '', $limit = 0, $offset = 0, $groupBy = '', $having = '')
    {
        return new self($orderBy, $limit, $offset, $groupBy, $having);
    }

    /**
     * Good to use for creating from a request or session array
     *
     *
     * @param $array
     * @param string $defaultOrderBy
     * @param int $defaultLimit
     * @param string $instanceId
     * @return Tool
     */
    static function createFromArray($array, $defaultOrderBy = '', $defaultLimit = 0, $instanceId= '')
    {
        $obj = new self($defaultOrderBy, $defaultLimit);
        $obj->setInstanceId($instanceId);

        if (isset($array[$obj->makeInstanceKey(Mapper::PARAM_OFFSET)])) {
            $obj->setOffset($array[$obj->makeInstanceKey(Mapper::PARAM_OFFSET)]);
        }
        if (isset($array[$obj->makeInstanceKey(Mapper::PARAM_ORDER_BY)])) {
            $obj->setOrderBy($array[$obj->makeInstanceKey(Mapper::PARAM_ORDER_BY)]);
        }
        if (isset($array[$obj->makeInstanceKey(Mapper::PARAM_LIMIT)])) {
            $obj->setLimit($array[$obj->makeInstanceKey(Mapper::PARAM_LIMIT)]);
            $obj->setOffset(0);
        }
        if (isset($array[$obj->makeInstanceKey(Mapper::PARAM_GROUP_BY)])) {
            $obj->setGroupBy($array[$obj->makeInstanceKey(Mapper::PARAM_GROUP_BY)]);
            $obj->setOffset(0);
        }
        if (isset($array[$obj->makeInstanceKey(Mapper::PARAM_HAVING)])) {
            $obj->setHaving($array[$obj->makeInstanceKey(Mapper::PARAM_HAVING)]);
            $obj->setOffset(0);
        }
        if (isset($array[$obj->makeInstanceKey(Mapper::PARAM_DISTINCT)])) {
            $obj->setDistinct($array[$obj->makeInstanceKey(Mapper::PARAM_DISTINCT)]);
        }

        return $obj;
    }

    /**
     * Use this to reload the tool from an array
     *
     * Use when creating from a session then load from the request to
     * create an updated tool.
     *
     * @param array $array
     * @return boolean Returns true if the object has been changed
     */
    public function updateFromArray($array)
    {
        $updated = false;

        if (isset($array[$this->makeInstanceKey(Mapper::PARAM_ORDER_BY)])) {
            if ($array[$this->makeInstanceKey(Mapper::PARAM_ORDER_BY)] != $this->getOrderBy()) {
                $this->setOrderBy($array[$this->makeInstanceKey(Mapper::PARAM_ORDER_BY)]);
                $updated = true;
            }
        }
        if (isset($array[$this->makeInstanceKey(Mapper::PARAM_LIMIT)])) {
            if ($array[$this->makeInstanceKey(Mapper::PARAM_LIMIT)] != $this->getLimit()) {
                $this->setLimit($array[$this->makeInstanceKey(Mapper::PARAM_LIMIT)]);
                $this->setOffset(0);
                $updated = true;
            }
        }
        if (isset($array[$this->makeInstanceKey(Mapper::PARAM_OFFSET)])) {
            if ($array[$this->makeInstanceKey(Mapper::PARAM_OFFSET)] != $this->getOffset()) {
                $this->setOffset($array[$this->makeInstanceKey(Mapper::PARAM_OFFSET)]);
                $updated = true;
            }
        }
        if (isset($array[$this->makeInstanceKey(Mapper::PARAM_GROUP_BY)])) {
            if ($array[$this->makeInstanceKey(Mapper::PARAM_GROUP_BY)] != $this->getGroupBy()) {
                $this->setGroupBy($array[$this->makeInstanceKey(Mapper::PARAM_GROUP_BY)]);
                $updated = true;
            }
        }
        if (isset($array[$this->makeInstanceKey(Mapper::PARAM_HAVING)])) {
            if ($array[$this->makeInstanceKey(Mapper::PARAM_HAVING)] != $this->getHaving()) {
                $this->setHaving($array[$this->makeInstanceKey(Mapper::PARAM_HAVING)]);
                $updated = true;
            }
        }
        if (isset($array[$this->makeInstanceKey(Mapper::PARAM_DISTINCT)])) {
            if ($array[$this->makeInstanceKey(Mapper::PARAM_DISTINCT)] != $this->isDistinct()) {
                $this->setDistinct($array[$this->makeInstanceKey(Mapper::PARAM_DISTINCT)]);
                $updated = true;
            }
        }

        return $updated;
    }


    /**
     * Get the current page number based on the limit and offset
     *
     * @return int
     */
    public function getPageNo()
    {
        return ceil($this->offset / $this->limit) + 1;
    }


    /**
     * Set the order By value
     *
     * @param string $str
     * @return $this
     */
    public function setOrderBy($str)
    {
        if (strstr(strtolower($str), 'field') === false) {
            $str = str_replace("'", "''", $str);
        }
        $this->orderBy = $str;
        return $this;
    }

    /**
     * Get the order by string for the DB queries
     *
     * @return string
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * Set the limit value
     *
     * @param int $i
     * @return $this
     */
    public function setLimit($i)
    {
        if ($i <= 0) $i = 0;
        $this->limit = (int)$i;
        return $this;
    }

    /**
     * Get the page limit
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Set the offset value
     *
     * @param int $i
     * @return $this
     */
    public function setOffset($i)
    {
        if ($i <= 0) $i = 0;
        $this->offset = (int)$i;
        return $this;
    }

    /**
     * Get the record offset
     *
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @return string
     */
    public function getGroupBy()
    {
        return $this->groupBy;
    }

    /**
     * @param string $groupBy
     * @return $this
     */
    public function setGroupBy($groupBy)
    {
        $this->groupBy = $groupBy;
        return $this;
    }

    /**
     * @return string
     */
    public function getHaving()
    {
        return $this->having;
    }

    /**
     * @param string $having
     * @return $this
     */
    public function setHaving($having)
    {
        $this->having = $having;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDistinct()
    {
        return $this->distinct;
    }

    /**
     * @param boolean $b
     * @return $this
     */
    public function setDistinct($b)
    {
        $this->distinct = $b;
        return $this;
    }

    /**
     * Return an array with the parameters
     * Useful to save the params to the session or request
     *
     * @return array
     */
    public function toArray()
    {
        $arr = array();
        if ($this->getOrderBy())
            $arr[$this->makeInstanceKey(Mapper::PARAM_ORDER_BY)] = $this->getOrderBy();
        if ($this->getLimit())
            $arr[$this->makeInstanceKey(Mapper::PARAM_LIMIT)] = $this->getLimit();
        if ($this->getOffset())
            $arr[$this->makeInstanceKey(Mapper::PARAM_OFFSET)] = $this->getOffset();
        if ($this->getGroupBy())
            $arr[$this->makeInstanceKey(Mapper::PARAM_GROUP_BY)] = $this->getGroupBy();
        if ($this->getHaving())
            $arr[$this->makeInstanceKey(Mapper::PARAM_HAVING)] = $this->getHaving();

        //$arr[$this->makeInstanceKey(Mapper::PARAM_DISTINCT)] = $this->isDistinct();
        return $arr;
    }


    /**
     * Return a string for the SQL query
     *
     * ORDER BY `cell`
     * LIMIT 10 OFFSET 30
     *
     * @param string $tblAlias
     * @return string
     */
    public function toSql($tblAlias = '')
    {
        // GROUP BY
        $groupBy = '';
        if ($this->getGroupBy()) {
            $groupBy = 'GROUP BY ' . str_replace(array(';', '-- ', '/*'), ' ', $this->getGroupBy());
        }

        // HAVING
        $having = '';
        if ($this->getHaving()) {
            $having = 'HAVING ' . str_replace(array(';', '-- ', '/*'), ' ', $this->getHaving());
        }

        // ORDER BY
        $orderBy = '';
        if ($this->getOrderBy()) {
            $orFields = str_replace(array(';', '-- ', '/*'), ' ', $this->getOrderBy());
            if ($tblAlias) {
                $arr = explode(',', $orFields);
                foreach ($arr as $i => $str) {
                    $str = trim($str);
                    if (preg_match('/^(ASC|DESC|FIELD\(|RAND\(|IF\(|NULL)/i', $str)) continue;
                    //if (!preg_match('/^([a-z]+\.)?`/i', $str)) continue;
                    //if (!preg_match('/^([a-zA-Z]+\.)/', $str) && is_string($str)) {
                    if (strpos($str, '.') === false) {
                        list($param, $order) = explode(' ', $str);
                        $str = $tblAlias . Pdo::quoteParameter($param) . ' ' . $order;
                    }
                    $arr[$i] = $str;
                }
                $orFields = implode(', ', $arr);
            }
            $orderBy = 'ORDER BY ' . $orFields;
        }

        // LIMIT
        $limitStr = '';
        if ($this->getLimit() > 0) {
            $limitStr = 'LIMIT ' . (int)$this->getLimit();
            if ($this->getOffset()) {
                $limitStr .= ' OFFSET ' . (int)$this->getOffset();
            }
        }
        return sprintf ('%s %s %s %s', $groupBy, $having, $orderBy, $limitStr);
    }
}