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


    public static function create(null|array|Filter $params): Filter
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
        return rtrim($default, ',');
    }

    public function setSelect(string $select): Filter
    {
        $this->select = $select;
        return $this;
    }

    public function prependSelect(string $select, ...$args): Filter
    {
        $this->select = vsprintf($select, $args) . $this->select;
//        if ($args)
//            $this->select = vsprintf($select, $args) . $this->select;
//        else
//            $this->select = $select . $this->select;
        return $this;
    }

    public function appendSelect(string $select, ...$args): Filter
    {
        $this->select .= vsprintf($select, $args);
//        if ($args)
//            $this->select .= vsprintf($select, $args);
//        else
//            $this->select .= $select;
        return $this;
    }

    public function getFrom(string $default = ''): string
    {
        $default = $this->from ?: $default;
        return rtrim(trim($default), ',');
    }

    public function setFrom(string $from): Filter
    {
        $this->from = $from;
        return $this;
    }

    public function prependFrom(string $from, ...$args): Filter
    {
        $this->from = vsprintf($from, $args) . $this->from;
//        if ($args)
//            $this->from = vsprintf($from, $args) . $this->from;
//        else
//            $this->from = $from . $this->from;
        return $this;
    }

    public function appendFrom(string $from, ...$args): Filter
    {
        $this->from .= vsprintf($from, $args);
//        if ($args)
//            $this->from .= vsprintf($from, $args);
//        else
//            $this->from .= $from;
        return $this;
    }

    public function getWhere(string $default = ''): string
    {
        $default = $this->where ?: $default;
        $default = trim($default);
        $default = rtrim($default, 'AND');
        return rtrim($default, 'OR');
    }

    public function setWhere(string $where): Filter
    {
        $this->where = $where;
        return $this;
    }

    public function prependWhere(string $where, ...$args): Filter
    {
        $this->where = vsprintf($where, $args) . $this->where;
//        if ($args)
//            $this->where = vsprintf($where, $args) . $this->where;
//        else
//            $this->where = $where . $this->where;
        return $this;
    }

    public function appendWhere(string $where, ...$args): Filter
    {
        $this->where .= vsprintf($where, $args);
//        if ($args)
//            $this->where .= vsprintf($where, $args);
//        else
//            $this->where .= $where;
        return $this;
    }

}