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
    protected $orderBy = '`id` DESC';

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
    protected $distinct = false;

    /**
     * @var string
     */
    protected $prepend = '';


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
     * Good to use for createing from a request or session array
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

        if (!empty($array[$obj->makeInstanceKey(Mapper::PARAM_ORDER_BY)])) {
            $obj->setOrderBy($array[$obj->makeInstanceKey(Mapper::PARAM_ORDER_BY)]);
        }
        if (!empty($array[$obj->makeInstanceKey(Mapper::PARAM_LIMIT)])) {
            $obj->setLimit($array[$obj->makeInstanceKey(Mapper::PARAM_LIMIT)]);
        }
        if (!empty($array[$obj->makeInstanceKey(Mapper::PARAM_OFFSET)])) {
            $obj->setOffset($array[$obj->makeInstanceKey(Mapper::PARAM_OFFSET)]);
        }
        if (!empty($array[$obj->makeInstanceKey(Mapper::PARAM_GROUP_BY)])) {
            $obj->setGroupBy($array[$obj->makeInstanceKey(Mapper::PARAM_GROUP_BY)]);
        }
        if (!empty($array[$obj->makeInstanceKey(Mapper::PARAM_HAVING)])) {
            $obj->setHaving($array[$obj->makeInstanceKey(Mapper::PARAM_HAVING)]);
        }

        return $obj;
    }

    /**
     * Use this to reload the tool from an array
     *
     * Use when creating from a session then load from the request to
     * create an updated tool.
     *
     * @return $this
     */
    public function updateFromArray()
    {
        if (!empty($array[$this->makeInstanceKey(Mapper::PARAM_ORDER_BY)])) {
            if ($array[$this->makeInstanceKey(Mapper::PARAM_ORDER_BY)] != $this->getOrderBy()) {
                $this->setOrderBy($array[$this->makeInstanceKey(Mapper::PARAM_ORDER_BY)]);
                $this->setOffset(0);
            }
        }
        if (!empty($array[$this->makeInstanceKey(Mapper::PARAM_LIMIT)])) {
            if ($array[$this->makeInstanceKey(Mapper::PARAM_LIMIT)] != $this->getLimit()) {
                $this->setLimit($array[$this->makeInstanceKey(Mapper::PARAM_LIMIT)]);
                $this->setOffset(0);
            }
        }
        if (!empty($array[$this->makeInstanceKey(Mapper::PARAM_OFFSET)])) {
            $this->setOffset($array[$this->makeInstanceKey(Mapper::PARAM_OFFSET)]);
        }

        if (!empty($array[$this->makeInstanceKey(Mapper::PARAM_GROUP_BY)])) {
            if ($array[$this->makeInstanceKey(Mapper::PARAM_GROUP_BY)] != $this->getGroupBy()) {
                $this->setGroupBy($array[$this->makeInstanceKey(Mapper::PARAM_GROUP_BY)]);
                $this->setOffset(0);
            }
        }
        if (!empty($array[$this->makeInstanceKey(Mapper::PARAM_HAVING)])) {
            if ($array[$this->makeInstanceKey(Mapper::PARAM_HAVING)] != $this->getHaving()) {
                $this->setHaving($array[$this->makeInstanceKey(Mapper::PARAM_HAVING)]);
                $this->setOffset(0);
            }
        }
        return $this;
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
     * @return $this;
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
     * @return $this;
     */
    public function setDistinct($b)
    {
        $this->distinct = $b;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrepend()
    {
        return $this->prepend;
    }

    /**
     * @param string $prepend
     * @return $this
     * @throws Exception
     */
    public function setPrepend($prepend)
    {
        $prepend = trim($prepend, '.');
        if (!preg_match('/[a-z0-9_]+/i', $prepend))
            throw new Exception('Invalid DB Tool prepend value');
        $this->prepend = $prepend.'.';
        return $this;
    }


    /**
     * Return a string for the SQL query
     *
     * ORDER BY `cell`
     * LIMIT 10 OFFSET 30
     *
     * @return string
     */
    public function getSql()
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
            if ($this->getPrepend()) {
                $arr = explode(',', $orFields);
                foreach ($arr as $i => $str) {
                    $str = trim($str);
                    if (preg_match('/^(ASC|DESC|FIELD\(|RAND\(|IF\(|NULL)/i', $str)) continue;
                    if (!preg_match('/^([a-z]+\.)?`/i', $str)) continue;
                    if (!preg_match('/^([a-zA-Z0-9_-]+\.)/', $str) && is_string($str)) {
                        $str = $this->getPrepend() . $str;
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