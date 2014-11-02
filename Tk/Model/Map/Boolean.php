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
class Boolean extends Iface
{
    
    /**
     * create a string
     *
     * @param string $propertyName 
     * @param array $columnNames (optional)
     * @return \Tk\Model\Map\Boolean
     */
    static function create($propertyName, $columnNames = array())
    {
        return new self($propertyName, $columnNames);
    }
    
    /**
     * getPropertyValue
     * 
     * @param array $row
     * @return bool Or null of not found
     */
    public function getPropertyValue($row)
    {
        $name = current($this->getColumnNames());
        if (isset($row[$name])) {
            return ($row[$name] == 1) ? true : false;
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
            $value = ($obj->$name === true) ? 1 : 0;
            return array($cname => $value);
        }
        return array($cname => 0);
    }
    
}
