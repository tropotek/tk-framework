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
 * The 6 character hex value is expected to be stored in the DB.
 * 
 *
 * @package Tk\Model\Map
 */
class Color extends Iface
{
    /**
     *
     * @param string $propertyName
     * @param array $columnName
     * @return \Tk\Model\Map\Color
     */
    static function create($propertyName, $columnNames = array())
    {
        return new self($propertyName, $columnNames);
    }
    
    
    /**
     * getPropertyValue
     * 
     * @param array $row
     * @return \Tk\Color Or null of not found
     */
    public function getPropertyValue($row)
    {
        $name = current($this->getColumnNames());
        if (isset($row[$name])) {
            return \Tk\Color::create($row[$name]);
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
            $value = $obj->$name->toString();
            return array($cname => enquote($value));
        }
        return array($cname => null);
    }
    

}
