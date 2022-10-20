<?php
namespace Tk\Db;


/**
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class Tool implements \Tk\InstanceKey
{

    const PARAM_GROUP_BY = 'groupBy';
    const PARAM_HAVING = 'having';
    const PARAM_ORDER_BY = 'orderBy';
    const PARAM_LIMIT = 'limit';
    const PARAM_OFFSET = 'offset';
    const PARAM_DISTINCT = 'distinct';
    const PARAM_FOUND_ROWS = 'foundRows';


    /**
     * Instance base id
     */
    protected string $instanceId = '';
    
    /**
     * Limit the number of records retrieved.
     * If > 0 then mapper should query for the total number of records
     */
    protected int $limit = 0;

    /**
     * The total number of rows found without LIMIT clause
     */
    protected int $foundRows = 0;

    /**
     * The record to start retrieval from
     */
    protected int $offset = 0;

    protected string $orderBy = 'id DESC';

    protected string $groupBy = '';

    protected string $having = '';

    protected bool $distinct = true;


    public function __construct(string $orderBy = '', int $limit = 0, int $offset = 0, string $groupBy = '', string $having = '')
    {
        $this->setOrderBy($orderBy);
        $this->setLimit($limit);
        $this->setOffset($offset);
        $this->setGroupBy($groupBy);
        $this->setHaving($having);
    }

    static function create(string $orderBy = '', int $limit = 0, int $offset = 0, string $groupBy = '', string $having = ''): Tool
    {
        return new self($orderBy, $limit, $offset, $groupBy, $having);
    }

    /**
     * Good to use when creating from a request or session array
     */
    static function createFromArray(array $array, string $defaultOrderBy = '', int $defaultLimit = 0, string $instanceId= ''): Tool
    {
        $obj = new self($defaultOrderBy, $defaultLimit);
        $obj->setInstanceId($instanceId);

        if (isset($array[$obj->makeInstanceKey(self::PARAM_OFFSET)])) {
            $obj->setOffset($array[$obj->makeInstanceKey(self::PARAM_OFFSET)]);
        }
        if (isset($array[$obj->makeInstanceKey(self::PARAM_ORDER_BY)])) {
            $obj->setOrderBy($array[$obj->makeInstanceKey(self::PARAM_ORDER_BY)]);
        }
        if (isset($array[$obj->makeInstanceKey(self::PARAM_LIMIT)])) {
            $obj->setLimit($array[$obj->makeInstanceKey(self::PARAM_LIMIT)]);
        }
        if (isset($array[$obj->makeInstanceKey(self::PARAM_GROUP_BY)])) {
            $obj->setGroupBy($array[$obj->makeInstanceKey(self::PARAM_GROUP_BY)]);
        }
        if (isset($array[$obj->makeInstanceKey(self::PARAM_HAVING)])) {
            $obj->setHaving($array[$obj->makeInstanceKey(self::PARAM_HAVING)]);
        }
        if (isset($array[$obj->makeInstanceKey(self::PARAM_DISTINCT)])) {
            $obj->setDistinct($array[$obj->makeInstanceKey(self::PARAM_DISTINCT)]);
        }
        return $obj;
    }

    /**
     * Use this to reload the tool from an array
     *
     * Use when creating from a session then load from the request to
     * create an updated tool.
     *
     * @return bool Returns true if the object has been changed
     */
    public function updateFromArray(array $array): bool
    {
        $updated = false;
        if (isset($array[$this->makeInstanceKey(self::PARAM_ORDER_BY)])) {
            if ($array[$this->makeInstanceKey(self::PARAM_ORDER_BY)] != $this->getOrderBy()) {
                $this->setOrderBy($array[$this->makeInstanceKey(self::PARAM_ORDER_BY)]);
            }
            $updated = true;
        }
        if (isset($array[$this->makeInstanceKey(self::PARAM_LIMIT)])) {
            if ($array[$this->makeInstanceKey(self::PARAM_LIMIT)] != $this->getLimit()) {
                $this->setLimit($array[$this->makeInstanceKey(self::PARAM_LIMIT)]);
                $this->setOffset(0);
            }
            $updated = true;
        }
        if (isset($array[$this->makeInstanceKey(self::PARAM_OFFSET)])) {
            if ($array[$this->makeInstanceKey(self::PARAM_OFFSET)] != $this->getOffset()) {
                $this->setOffset($array[$this->makeInstanceKey(self::PARAM_OFFSET)]);
            }
            $updated = true;
        }
        if (isset($array[$this->makeInstanceKey(self::PARAM_GROUP_BY)])) {
            if ($array[$this->makeInstanceKey(self::PARAM_GROUP_BY)] != $this->getGroupBy()) {
                $this->setGroupBy($array[$this->makeInstanceKey(self::PARAM_GROUP_BY)]);
            }
            $updated = true;
        }
        if (isset($array[$this->makeInstanceKey(self::PARAM_HAVING)])) {
            if ($array[$this->makeInstanceKey(self::PARAM_HAVING)] != $this->getHaving()) {
                $this->setHaving($array[$this->makeInstanceKey(self::PARAM_HAVING)]);
            }
            $updated = true;
        }
        if (isset($array[$this->makeInstanceKey(self::PARAM_DISTINCT)])) {
            if ($array[$this->makeInstanceKey(self::PARAM_DISTINCT)] != $this->isDistinct()) {
                $this->setDistinct($array[$this->makeInstanceKey(self::PARAM_DISTINCT)]);
            }
            $updated = true;
        }

        return $updated;
    }


    /**
     * Get the current page number based on the limit and offset
     */
    public function getPageNo(): int
    {
        return ceil($this->offset / $this->limit) + 1;
    }

    public function getFoundRows(): int
    {
        return $this->foundRows;
    }

    public function setFoundRows(int $foundRows): Tool
    {
        $this->foundRows = $foundRows;
        return $this;
    }

    /**
     * Set the order By value
     */
    public function setOrderBy(string $str): Tool
    {
        $this->orderBy = $str;
        return $this;
    }

    /**
     * Get the order by string for the DB queries
     */
    public function getOrderBy(): string
    {
        return $this->orderBy;
    }

    /**
     * Get the order by property if available and can be found
     */
    public function getOrderProperty(): string
    {
        $order = $this->getOrderBy();
        if ($order && !preg_match('/^(ASC|DESC|FIELD\(|IFNULL\(|RAND\(|IF\(|NULL|CASE)/', $order)) {
            if (preg_match('/^([a-z0-9]+\.)?([a-z0-9_\-]+)/i', $order, $regs)) {
                $order = trim($regs[2]);
            }
        }
        return $order;
    }

    /**
     * Set the limit value
     */
    public function setLimit(int $i): Tool
    {
        if ($i <= 0) $i = 0;
        $this->limit = $i;
        return $this;
    }

    /**
     * Get the page limit
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * Set the offset value
     */
    public function setOffset(int $i): Tool
    {
        if ($i <= 0) $i = 0;
        $this->offset = $i;
        return $this;
    }

    /**
     * Get the record offset
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getGroupBy(): string
    {
        return $this->groupBy;
    }

    public function setGroupBy(string $groupBy): Tool
    {
        $this->groupBy = $groupBy;
        return $this;
    }

    public function getHaving(): string
    {
        return $this->having;
    }

    public function setHaving(string $having): Tool
    {
        $this->having = $having;
        return $this;
    }

    public function isDistinct(): bool
    {
        return $this->distinct;
    }

    public function setDistinct(bool $b): Tool
    {
        $this->distinct = $b;
        return $this;
    }

    /**
     * Return an array with the parameters
     * Useful to save the params to the session or request
     */
    public function toArray(): array
    {
        $arr = [];
        $arr[$this->makeInstanceKey(self::PARAM_ORDER_BY)] = $this->getOrderBy();
        $arr[$this->makeInstanceKey(self::PARAM_LIMIT)] = $this->getLimit();
        $arr[$this->makeInstanceKey(self::PARAM_OFFSET)] = $this->getOffset();
        if ($this->getGroupBy())
            $arr[$this->makeInstanceKey(self::PARAM_GROUP_BY)] = $this->getGroupBy();
        if ($this->getHaving())
            $arr[$this->makeInstanceKey(self::PARAM_HAVING)] = $this->getHaving();

        return $arr;
    }


    /**
     * NOTE: If your object uses the Model uses \Tk\Db\Mapper it is best to use that ...Mapper::getToolSql($tool)
     * Return a string for the SQL query
     *
     * ORDER BY `cell`
     * LIMIT 10 OFFSET 30
     *
     * @note: We have an issue if we want to get the SQL query and there is no mapper,
     *        maybe we should retain the tool toSql() function for now.
     */
    public function toSql(string $tblAlias = '', Pdo $db = null): string
    {
        // GROUP BY
        $groupBy = '';
        if ($this->getGroupBy()) {
            $groupBy = 'GROUP BY ' . str_replace([';', '-- ', '/*'], ' ', $this->getGroupBy());
        }

        // HAVING
        $having = '';
        if ($this->getHaving()) {
            $having = 'HAVING ' . str_replace([';', '-- ', '/*'], ' ', $this->getHaving());
        }

        // ORDER BY
        $orderBy = '';
        if ($this->getOrderBy()) {
            $orFields = trim(str_replace([';', '-- ', '/*'], ' ', $this->getOrderBy()));
            if ($tblAlias && $db) {
                if (!str_contains($tblAlias, '.')) {
                    $tblAlias = $tblAlias . '.';
                }
                if (!preg_match('/^(ASC|DESC|FIELD\(|\'|RAND|CONCAT|SUBSTRING\(|IF\(|NULL|CASE)/i', $orFields)) {
                    $arr = explode(',', $orFields);
                    foreach ($arr as $i => $str) {
                        $str = trim($str);
                        if (preg_match('/^(ASC|DESC|FIELD\(|\'|RAND|CONCAT|SUBSTRING\(|IF\(|NULL|CASE)/i', $str)) continue;
                        if (!str_contains($str, '.')) {
                            $a = explode(' ', $str);
                            $str = $tblAlias . $db->quoteParameter($a[0]);
                            if (isset($a[1])) {
                                $str = $str . ' ' . $a[1];
                            }
                        }
                        $arr[$i] = $str;
                    }
                    $orFields = implode(', ', $arr);
                }
            }
            $orderBy = 'ORDER BY ' . $orFields;
        }

        // LIMIT
        $limitStr = '';
        if ($this->getLimit() > 0) {
            $limitStr = 'LIMIT ' . $this->getLimit();
            if ($this->getOffset()) {
                $limitStr .= ' OFFSET ' . $this->getOffset();
            }
        }
        $sql = sprintf ('%s %s %s %s', $groupBy, $having, $orderBy, $limitStr);
        return $sql;
    }

    /**
     * Create a unique object instance key that can be used
     * to lookup and find it within an array or session, etc
     *
     * @example `{_instanceId}_{key}`
     */
    public function makeInstanceKey(string $key): string
    {
        if ($this->instanceId)
            return $this->instanceId . '-' . $key;
        return $key;
    }

    public function setInstanceId(string $str)
    {
        $this->instanceId = $str;
    }

}