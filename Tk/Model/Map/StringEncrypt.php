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
class StringEncrypt extends Iface
{
    /**
     * @var string 
     */
    private $key = '';
    
    
    
    /**
     * create a string
     *
     * @param string $propertyName 
     * @param array $columnNames (optional)
     * @return \Tk\Model\Map\EncryptString
     */
    static function create($propertyName, $columnNames = array(), $key = null)
    {
        $obj = new self($propertyName, $columnNames);
        $obj->key = \Tk\Encrypt::$key;
        if ($key) {
            $obj->key = $key;
        }
        return $obj;
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
            return \Tk\Encrypt::decode($row[$name], $this->key);
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
            return array($cname => enquote(\Tk\Encrypt::encode($value, $this->key)));
        }
        return array($cname => enquote(''));
    }
    
}
