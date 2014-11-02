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
class Date extends Iface
{
    /**
     *
     * @param string $propertyName
     * @param array $columnName
     * @return \Tk\Model\Map\Date
     */
    static function create($propertyName, $columnNames = array())
    {
        return new self($propertyName, $columnNames);
    }
    
    
    /**
     * getPropertyValue
     * 
     * @param array $row
     * @return \Tk\Date Or null of not found
     */
    public function getPropertyValue($row)
    {
        $name = current($this->getColumnNames());
        if (isset($row[$name])) {
            return \Tk\Date::create($row[$name]);
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
        if ($obj->$name instanceof \Tk\Date) {
            $value = $obj->$name->toString();
            return array($cname => enquote($value));
        }
        return array($cname => 'NULL');
    }
    

}
