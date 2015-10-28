<?php
/*
 * Created by PhpStorm.
 * User: godar
 * Date: 10/28/15
 * Time: 4:23 PM
 */

namespace Tk\Db;

/**
 * Class Params
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
class Params
{
    const SID = 'dbt-';

    const PARAM_GROUP_BY = 'groupBy';
    const PARAM_HAVING = 'having';
    const PARAM_ORDER_BY = 'orderBy';
    const PARAM_LIMIT = 'limit';
    const PARAM_OFFSET = 'offset';
    const PARAM_FOUND_ROWS = 'foundRows';

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
     * The total number of rows found without LIMIT clause
     * @var int
     */
    private $foundRows = 0;




    /**
     * __construct
     *
     * @param string $orderBy
     * @param int $limit
     * @param int $offset
     * @param int $foundRows
     */
    public function __construct($orderBy = '', $limit = 0, $offset = 0, $foundRows = 0)
    {
        $this->setOrderBy($orderBy);
        $this->setLimit($limit);
        $this->setOffset($offset);
        $this->setFoundRows($foundRows);
    }

    /**
     * Create a listParams object from a request object
     *
     * @param string $orderBy
     * @param int $limit
     * @param int $offset
     * @param int $foundRows
     * @return \Tk\Db\Tool
     */
    static function create($orderBy = '', $limit = 0, $offset = 0 , $foundRows = 0)
    {
        return new self($orderBy, $limit, $offset, $foundRows);
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
        $this->offset = (int)$i;
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
     * Set the number of rows found
     *
     * @param int $i
     * @return $this
     */
    public function setFoundRows($i)
    {
        $this->foundRows = (int)$i;
        return $this;
    }

    /**
     * Get the total record count.
     * This value will be the available count without a limit.
     * If hasTotal() is false however this value will be the total number of
     * records retrieved.
     *
     * @return int
     */
    public function getFoundRows()
    {
        return $this->foundRows;
    }

}