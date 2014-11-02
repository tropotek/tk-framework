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
class Geo extends Iface
{
    /**
     *
     * @param string $propertyName
     * @param array $columnName
     * @return \Tk\Model\Map\Geo
     */
    static function create($propertyName, $columnNames = array('mapLat', 'mapLng', 'mapZoom'))
    {
        return new self($propertyName, $columnNames);
    }
    
    
    /**
     * getPropertyValue
     * 
     * @param array $row
     * @return \Tk\Geo Or null of not found
     */
    public function getPropertyValue($row)
    {
        $geo = new \Tk\Geo();
        foreach ($this->getColumnNames() as $name)
        {
            $method = 'set'.ucfirst(str_replace($this->getPropertyName(), '', $name));
            if (isset($row[$name])) {
                $geo->$method($row[$name]);
            }
        }
        return $geo;
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
        if ($obj->$name instanceof \Tk\Geo) {
            $geo = $obj->$name;
            return array($cname.'Lat' => $geo->getLat(), $cname.'Lng' => $geo->getLng(), $cname.'Zoom' => $geo->getZoom());
        }
        return array($cname.'Lat' => 0, $cname.'Lng' => 0, $cname.'Zoom' => 12);
    }
    

}
