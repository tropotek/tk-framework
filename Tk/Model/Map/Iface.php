<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Model\Map;

/**
 * This object is the base column mapper for object properties
 * so the object loader can serialize and unserialize objects from supplied arrays
 * 
 * @package Tk\Model\Map
 */
abstract class Iface extends \Tk\Object
{
    
    /**
     * @var string
     */
    protected $columnNames = array();
    
    /**
     * @var string
     */
    protected $propertyName = '';
    
    /**
     * Is this map an auto index field.
     * IE: Does its value get incremented/set externally
     * @var bool
     */
    protected $index = false;


    /**
     * __construct
     *
     * @param string $propertyName The object property to map the column to.
     * @param array $columnNames
     * @internal param array $columnName The DB column names to map the object to.
     */
    public function __construct($propertyName, $columnNames = array())
    {
        $this->propertyName = $propertyName;
        if (!$columnNames) {
            $columnNames = array($propertyName);
        }
        $this->columnNames = $columnNames;
        if (!is_array($this->columnNames)) {
            $this->columnNames = array($this->columnNames);
        }
    }
    
    /**
     * The object's instance property name
     *
     * @return string
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }
    
    /**
     * This is the data source column name.
     * EG: $row('column1' => 10, 'column2' => 'string', 'column3' => 1.00);
     * The source column names are 'column1', 'column2' and 'column 3'
     *
     * @return array
     */
    public function getColumnNames()
    {
        return $this->columnNames;
    }
    
    
    /**
     * Return an object form the DB source row
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
     * Return an array map of values containing the storage raw values
     *
     * @param \Tk\Model\Model $obj
     * @return array
     */
    public function getColumnValue($obj)
    {
        $pname = $this->getPropertyName();
        $cname = current($this->getColumnNames());

        if (isset($obj->$pname)) {
            $value = $obj->$pname;

            return array($cname => $value);
        }
        return array($cname => enquote(''));
    }
    
    /**
     * Is this map an auto index field.
     * IE: Does its value get incremented/set externally
     * 
     * @return bool
     */
    public function isIndex()
    {
        return $this->index;
    }
    
    /**
     * Is this map an auto index field.
     * IE: Does its value get incremented/set externally
     *
     * @param bool $b
     * @return \Tk\Model\Map\Iface
     */
    public function setIndex($b)
    {
        $this->index = $b;
        return $this;
    }
    
}
