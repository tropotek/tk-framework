<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Model\Map;

/**
 * Use this map to serialise/unserialise data to/from a base64 encoded string
 *
 * @package Tk\Model\Map
 */
class Serial extends Iface
{
    /**
     * create a string
     *
     * @param string $propertyName 
     * @param array $columnNames (optional)
     * @return \Tk\Model\Map\Serial
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
            return unserialize(base64_decode($row[$name]));
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
            $value = base64_encode(serialize($obj->$name));
            return array($cname => enquote($value));
        }
        return array($cname => enquote(''));
    }

}
