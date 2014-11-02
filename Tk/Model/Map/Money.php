<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Model\Map;

/**
 * A column map
 *
 * All money is assumed to be stored in the DB in it units
 * For AUD $1.00 will be save in the DB as 100.
 *
 *
 * @package Tk\Model\Map
 */
class Money extends Iface
{
    /**
     *
     * @param string $propertyName
     * @param array $columnName
     * @return \Tk\Model\Map\Money
     */
    static function create($propertyName, $columnNames = array())
    {
        return new self($propertyName, $columnNames);
    }

    /**
     * getPropertyValue
     *
     * @param array $row
     * @return \Tk\Money Or null of not found
     */
    public function getPropertyValue($row)
    {
        $name = current($this->getColumnNames());
        if (isset($row[$name])) {
            return \Tk\Money::create($row[$name]);
        }
    }

    /**
     * Get the storage value
     *
     * @param \Tk\Model\Model $obj
     * @return int
     */
    public function getColumnValue($obj)
    {
        $name = $this->getPropertyName();
        $cname = current($this->getColumnNames());
        if (isset($obj->$name)) {
            $value = (int)$obj->$name->getAmount();
            return array($cname => $value);
        }
        return array($cname => null);
    }


}
