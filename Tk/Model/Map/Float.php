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
 * @package Tk\Model\Map
 */
class Float extends Iface
{
    
    /**
     * create a string
     *
     * @param string $propertyName 
     * @param array $columnName (optional)
     * @return \Tk\Model\Map\Float
     */
    static function create($propertyName, $columnNames = array())
    {
        return new self($propertyName, $columnNames);
    }
    
    
    /**
     * getPropertyValue
     * 
     * @param array $row
     * @return float Or null of not found 
     */
    public function getPropertyValue($row)
    {
        $name = current($this->getColumnNames());
        if (isset($row[$name])) {
            return (float)$row[$name];
        }
    }
    
    /**
     * Get the storage value
     * 
     * @param \Tk\Model\Model $obj
     * @return float
     */
    public function getColumnValue($obj)
    {
        $name = $this->getPropertyName();
        $cname = current($this->getColumnNames());
        if (isset($obj->$name)) {
            $value = (float)$obj->$name;
            return array($cname => $value);
        }
        return array($cname => 0.0);
    }
    
}
