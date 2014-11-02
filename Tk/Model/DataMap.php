<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Model;

/**
 * A data map class that maps database tables and columns to
 *   Objects and properties.
 *
 * @package Tk\Model
 */
class DataMap extends \Tk\Object
{
    
    /**
     * @var array
     */
    private $propertyMaps = array();
    
    /**
     * @var array
     */
    private $idPropertyMaps = array();
    
    /**
     * @var string
     */
    protected $class = '';
    
    
    
    
    /**
     * __construct
     *
     * @param string $class
     */
    public function __construct($class)
    {
        if (substr($class, -3) == 'Map') {
            $class = substr($class, 0, -3);
        }
        $this->class = $class;
    }

    /**
     * loadObject
     * An array map should be used to load the object
     * EG:
     * <code>
     *   array(
     *     'propertyName1' => 'propertyvalue1',
     *     'propertyName2' => 'propertyvalue2'
     *   );
     * </code>
     *
     * @param array $row
     * @param \Tk\Db\Model $object
     * @throws \Tk\IllegalArgumentException
     * @return \Tk\Db\Model
     */
    public function loadObject($row, $object = null)
    {
        if ($object && !$object instanceof Model) {
            throw new \Tk\IllegalArgumentException('Cannot load a non Model object.');
        }
        if (!$object) {
            $class = $this->getMapClass();
            $obj = new $class();
        }
        /* @var $map \Tk\Model\Map\Iface */
        foreach ($this->getAllProperties() as $map) {
            $value = $map->getPropertyValue($row);
            $name = $map->getPropertyName();
            $obj->$name = $value;
        }
        return $obj;
    }


    /**
     * Convert a Db object to a native array
     * This data should be equivelent to an associative row
     * return from a db query.
     *
     * @param Model $obj
     * @throws \Tk\IllegalArgumentException
     * @return array
     */
    public function toArray(Model $obj)
    {
        if ($obj->getClassName() != $this->getMapClass()) {
            throw new \Tk\IllegalArgumentException('Wrong map for this class: ' . $obj->getClassName() . ' ['.$this->getMapClass().']');
        }
        $row = array();
        /* @var $map \Tk\Model\Map\Iface */
        foreach ($this->getAllProperties() as $map) {
            $vals =  $map->getColumnValue($obj);
            foreach($map->getColumnNames() as $name) {
                if (substr($vals[$name], 0, 1) == "'") {
                    $row[$name] = substr($vals[$name], 1, -1);
                } else {
                    $row[$name] = $vals[$name];
                }
            }
        }
        return $row;
    }

    
    
    /**
     * Get the class for this data map
     *
     * @return string
     */
    public function getMapClass()
    {
        return $this->class;
    }
    
    /**
     * Load all property maps into one array
     *
     * @return array
     */
    public function getAllProperties()
    {
        return array_merge($this->getIdPropertyList(), $this->getPropertyList());
    }
    
    /**
     * Gets the object ID columns.
     *
     * @return array An associative array of ID columns indexed by property.
     */
    public function getIdPropertyList()
    {
        return $this->idPropertyMaps;
    }
    
    /**
     * Gets the list of property mappers.
     *
     * @return array
     */
    public function getPropertyList()
    {
        return $this->propertyMaps;
    }

    /**
     * Gets a property map by its name
     *
     * @param $name
     * @return \Tk\Model\Map\Iface
     */
    public function getProperty($name)
    {
        if (isset($this->propertyMaps[$name])) {
            return $this->propertyMaps[$name];
        }
    }
    
    /**
     * Adds an object ID property
     *
     * @param \Tk\Model\Map\Iface $propertyMap
     * @param bool $isIndex
     */
    public function addIdProperty($propertyMap, $isIndex=true)
    {
        $propertyMap->setIndex($isIndex);
        $this->idPropertyMaps[$propertyMap->getPropertyName()] = $propertyMap;
    }
    
    /**
     * Add a property to this map
     *
     * @param \Tk\Model\Map\Iface $propertyMap
     */
    public function addProperty($propertyMap)
    {
        $this->propertyMaps[$propertyMap->getPropertyName()] = $propertyMap;
    }
    
}