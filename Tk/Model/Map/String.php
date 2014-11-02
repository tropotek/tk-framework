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
class String extends Iface
{
    
    /**
     * create a string
     *
     * @param string $propertyName 
     * @param array $columnNames (optional)
     * @return \Tk\Model\Map\String
     */
    static function create($propertyName, $columnNames = array())
    {
        return new self($propertyName, $columnNames);
    }
    
    /**
     * getPropertyValue
     * 
     * @param array $row
     * @return mixed 
     */
    public function getPropertyValue($row)
    {
        $name = current($this->getColumnNames());
        if (isset($row[$name])) {
            return $row[$name];
        }
    }
    
    /**
     * Get the storage value
     * 
     * @param mixed $obj
     * @return string 
     */
    public function getColumnValue($obj)
    {
        $name = $this->getPropertyName();
        $cname = current($this->getColumnNames());
        if (isset($obj->$name)) {
            $value = $obj->$name;
            return array($cname => enquote($value));
        }
        return array($cname => enquote(''));
    }
    
}

