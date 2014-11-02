<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Db;

/**
 * The TOOL object is named from the params (Total, Offset, OrderBy, Limit)
 *
 * This object manages a query's params $orderBy, $limit, $offset and $total
 * where total is the total number of records available without a limit.
 *
 * Useful for persistant storage of table data and record positions
 *
 * @package Tk
 */
class Tool extends \Tk\Object
{
    const SID = 'dbt-';

    /**
     * The request parameter keys
     */
    const REQ_LIMIT = 'limit';
    const REQ_OFFSET = 'offset';
    const REQ_ORDER_BY = 'orderBy';

    /**
     * Limit the number of records retrieved.
     * If > 0 then mapper should query for the total number of records
     *
     * @var int
     */
    private $limit = 0;

    /**
     * The record to start retrieval from
     *
     * @var int
     */
    private $offset = 0;

    /**
     * @var string
     */
    private $orderBy = '`id` DESC';

    /**
     * The total number of available records without LIMIT clause
     * @var int
     */
    private $total = 0;





    /**
     * __construct
     *
     * @param string $orderBy
     * @param int $limit
     * @param int $offset
     * @param int $total
     */
    public function __construct($orderBy = '', $limit = 0, $offset = 0, $total = 0)
    {
        $this->setOrderBy($orderBy);
        $this->setLimit($limit);
        $this->setOffset($offset);
        $this->setTotal($total);
    }

    /**
     * Create a listParams object from a request object
     *
     * @param string $orderBy
     * @param int $limit
     * @param int $offset
     * @param int $total
     * @return \Tk\Db\Tool
     */
    static function create($orderBy = '', $limit = 0, $offset = 0 , $total = 0)
    {
        return new self($orderBy, $limit, $offset, $total);
    }

    /**
     * Create a listParams object from a request object
     *
     * @param int $instanceId This is used to create the unique request key
     * @param string $orderBy The default orderby to use
     * @param int $limit
     * @return \Tk\Db\Tool
     *
     * @deprecated This is an over complex factory method,
     * @todo see if we can do without it, move this functionality to the Table module.
     */
    static function createFromRequest($instanceId = null, $orderBy = '', $limit = 50)
    {
        //self::clearSession();
        $tool = new self($orderBy, $limit, 0);
        $tool->setInstanceId($instanceId);
        if ($tool->getSession()->exists($tool->getSessionHash())) {
            $tool = $tool->getSession()->get($tool->getSessionHash());
        }

        $request = $tool->getRequest();
        $reqMod = false;
        if ($request->exists($tool->getObjectKey(self::REQ_OFFSET))) {
            $tool->setOffset($request->get($tool->getObjectKey(self::REQ_OFFSET)));
            $reqMod = true;
        }
        if ($request->exists($tool->getObjectKey(self::REQ_LIMIT))) {
            $tool->setLimit($request->get($tool->getObjectKey(self::REQ_LIMIT)));
            $tool->setOffset(0);
            $reqMod = true;
        }
        if ($request->exists($tool->getObjectKey(self::REQ_ORDER_BY))) {
            $tool->setOrderBy($request->get($tool->getObjectKey(self::REQ_ORDER_BY)));
            $tool->setOffset(0);
            $reqMod = true;
        }

        $tool->getSession()->set($tool->getSessionHash(), $tool);
        if ($reqMod && $tool->getRequest()->getRequestMethod() == 'GET') {
            $tool->getRequest()->getRequestUri()->delete($tool->getObjectKey(self::REQ_OFFSET))->
                    delete($tool->getObjectKey(self::REQ_LIMIT))->delete($tool->getObjectKey(self::REQ_ORDER_BY))->redirect();
        }
        return $tool;
    }



    /**
     * Get a unique session name for this table
     *
     * @return string
     */
    public function getSessionHash()
    {
        return self::SID . md5($this->getUri()->getPath(true).'-'.$this->getInstanceId());
    }

    /**
     * Delete the Db tool objects from the session if it exists.
     *
     * @return bool Returns true if the object was deleted from the session
     */
    static function clearSession()
    {
        $ses = \Tk\Session::getInstance()->getAllParams();
        foreach ($ses as $k => $v) {
            if (preg_match('/^'.preg_quote(self::SID).'[a-z0-9]+/i', $k)) {
                unset($_SESSION[$k]);
            }
        }
    }

    /**
     * Reset the offest to 0
     *
     * @return \Tk\Db\Tool
     */
    public function reset()
    {
        $this->offset = 0;
        $this->limit = 0;
        $this->orderBy = '';
        return $this;
    }

    /**
     * Set the order By value
     *
     * @param string $str
     * @return \Tk\Db\Tool
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
     * @return \Tk\Db\Tool
     */
    public function setLimit($i)
    {
        $this->limit = (int)$i;
        if ($this->limit < 0) {
            $this->limit = 0;
        }
        return $this;
    }

    /**
     * Get the page limit for pagenators and queries
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
     * @return \Tk\Db\Tool
     */
    public function setOffset($i)
    {
        $this->offset = (int)$i;
        if ($this->offset < 0) {
            $this->offset = 0;
        }
        return $this;
    }

    /**
     * Get the record offset for pagenators and queries
     *
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Set the total value
     *
     * @param int $i
     * @return \Tk\Db\Tool
     */
    public function setTotal($i)
    {
        $this->total = (int)$i;
        return $this;
    }

    /**
     * Get the total record count.
     * This value will be the available count without a limit.
     * If hasTotal() is false however this value will be the total number of
     * records retrieved.
     *
     * Change the setMode() to change the behaviour of total value.
     *
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
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
     * Return a string for the SQL query
     *
     * ORDER BY `cell`
     * LIMIT 10 OFFSET 30
     *
     * @param string $prepend  If set will be prepended to any fields without a prefix string.
     * @return string
     */
    public function getSql($prepend = '')
    {
        $orderBy = '';
        if ($this->getOrderBy()) {
            $orFields = str_replace(array(';', '-- ', '/*'), ' ', $this->getOrderBy());
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
        if ($this->getLimit() > 0) {
            $limitStr = 'LIMIT ' . (int)$this->getLimit();
            if ($this->getOffset()) {
                $limitStr .= ' OFFSET ' . (int)$this->getOffset();
            }
        }
        return $orderBy . ' ' . $limitStr;
    }

}
