<?php
/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Model;

/**
 * A base Data Model Object.
 *
 * NOTICE: All model objects should not use `private` for field properties.
 * This will cause fields not to load from the storage, only public and protected
 * properties are supported with this data mapper.
 *
 * @package Tk
 */
abstract class Model extends \Tk\Object
{


    /**
     * Get the model Id
     * Usually the primary key of a DB
     *
     * @return mixed
     */
    public function getId()
    {
        if (property_exists($this, 'id')) {
            return $this->id;
        }
        return 0;
    }

    /**
     * Getter
     * This is used for anon objects and to facilitate data type loading
     *
     * @param $property
     * @throws \Tk\Exception
     * @internal param string $var
     * @return mixed
     * @todo: move all this to the model data loader......
     */
    public function __get($property)
    {
        $refClass = new \ReflectionClass($this->getClassName());
        if ($refClass->hasProperty($property)) {
            if (property_exists($this, $property)) {
                return $this->$property;
            }
        }
        throw new \Tk\Exception('Parameter does not exist: ' . $this->getClassName() . '::' . $property);
    }

    /**
     * Setter
     * This is used for anon objects and to facilitate data type loading
     *
     * @param string $property
     * @param mixed $value
     * @throws \Tk\Exception
     * @return mixed
     * @todo: move all this to the model data loader......
     */
    public function __set($property, $value)
    {
        $refClass = new \ReflectionClass($this->getClassName());
        if ($refClass->hasProperty($property)) {
            if ($refClass->hasProperty($property)) {
                $refProperty = $refClass->getProperty($property);
                if ($refProperty->isPrivate()) {
                    $refProperty->setAccessible(true);
                }
                $refProperty->setValue($this, $value);
                if ($refProperty->isPrivate()) {
                    $refProperty->setAccessible(false);
                }
            }
        }

        throw new \Tk\Exception('Parameter does not exist: ' . $this->getClassName() . '::' . $property);
    }

    /**
     * Try to validate an object if as validator object available
     *
     * @return \Tk\Validator
     */
    public function getValidator()
    {
        $class = get_class($this) . 'Validator';
        if (class_exists($class)) {
            return new $class($this);
        }
    }

    /**
     * Return an array version of this object.
     *
     * @return array
     */
    public function toArray()
    {
        $arr = array();
        foreach (get_class_vars($this) as $k => $v) {
            $arr[$k] = $v;
        }
        return $arr;
    }
}