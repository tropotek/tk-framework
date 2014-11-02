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
class Integer extends Iface
{
    
    /**
     * create a string
     *
     * @param string $propertyName 
     * @param array $columnNames (optional)
     * @return \Tk\Model\Map\Integer
     */
    static function create($propertyName, $columnNames = array())
    {
        return new self($propertyName, $columnNames);
    }
    
    
    /**
     * getPropertyValue
     * 
     * @param array $row
     * @return int Or null of not found
     */
    public function getPropertyValue($row)
    {
        $name = current($this->getColumnNames());
        if (isset($row[$name])) {
            return (int)$row[$name];
        }
    }
    
    /**
     * Get the storage value
     * 
     * @param \Tk\Model\Model $obj
     * @return string 
     */
    public function getColumnValue($obj)
    {
        $name = $this->getPropertyName();
        $cname = current($this->getColumnNames());
        if (isset($obj->$name)) {
            $value = (int)$obj->$name;
            return array($cname => $value);
        }
        return array($cname => 0);
    }
    
}
