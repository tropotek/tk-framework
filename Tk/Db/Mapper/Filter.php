<?php

namespace Tk\Db\Mapper;

/**
 * Use this object to enhance your Mapper filtered queries
 */
class Filter extends \Tk\Collection
{

    protected string $select = '';

    protected string $from = '';

    protected string $where = '';


    /**
     * @param null|array|Filter $params
     */
    public static function create($params): Filter
    {
        if ($params instanceof Filter) return $params;
        $obj = new self();
        $obj->replace($params);
        return $obj;
    }

    public function getSelect(string $default = ''): string
    {
        $default = $this->select ?: $default;
        $default = trim($default);
        $default = rtrim($default, ',');
        return $default;
    }

    public function setSelect(string $select): Filter
    {
        $this->select = $select;
        return $this;
    }

    public function prependSelect(string $select, ...$args): Filter
    {
        if ($args)
            $this->select = vsprintf($select, $args) . $this->select;
        else
            $this->select = $select . $this->select;
        return $this;
    }

    public function appendSelect(string $select, ...$args): Filter
    {
        if ($args)
            $this->select .= vsprintf($select, $args);
        else
            $this->select .= $select;
        return $this;
    }

    public function getFrom(string $default = ''): string
    {
        $default = $this->from ?: $default;
        $default = rtrim(trim($default), ',');
        return $default;
    }

    public function setFrom(string $from): Filter
    {
        $this->from = $from;
        return $this;
    }

    public function prependFrom(string $from, ...$args): Filter
    {
        if ($args)
            $this->from = vsprintf($from, $args) . $this->from;
        else
            $this->from = $from . $this->from;
        return $this;
    }

    public function appendFrom(string $from, ...$args): Filter
    {
        if ($args)
            $this->from .= vsprintf($from, $args);
        else
            $this->from .= $from;
        return $this;
    }

    public function getWhere(string $default = ''): string
    {
        $default = $this->where ?: $default;
        $default = trim($default);
        $default = rtrim($default, 'AND');
        $default = rtrim($default, 'OR');
        return $default;
    }

    public function setWhere(string $where): Filter
    {
        $this->where = $where;
        return $this;
    }

    public function prependWhere(string $where, ...$args): Filter
    {
        if ($args)
            $this->where = vsprintf($where, $args) . $this->where;
        else
            $this->where = $where . $this->where;
        return $this;
    }

    public function appendWhere(string $where, ...$args): Filter
    {
        if ($args)
            $this->where .= vsprintf($where, $args);
        else
            $this->where .= $where;
        return $this;
    }

}