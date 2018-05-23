<?php
namespace Tk;

/**
 * Class ClassTool
 * This object is a utility object to perform actions 
 * with class names and name-spacing issues.
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class ObjectUtil
{

    /**
     * Check if an object property exists ignoring the scope of that property.
     *
     * NOTE: Accessing private properties is possible, but care must be taken
     * if that private property was defined lower into the inheritance chain.
     * For example, if class A extends class B, and class B defines a private
     * property called 'foo', getProperty will throw a ReflectionException.
     *
     * Instead, you can loop over getParentClass until it returns false to
     * look for the private property, at which point you can access and/or
     * modify its value as needed. (modify this method if needed)
     *
     * @param ObjectUtil $object
     * @param string $name the property name
     * @return bool
     */
    public static function objectPropertyExists($object, $name)
    {
        try {
            $reflect = new \ReflectionClass($object);
            return $reflect->hasProperty($name);
        } catch (\Exception $e) {}

        return false;
    }

    /**
     * Allows for getting of an objects property value either through a getter method or by
     * directly accessing the property itself ignoring the scope permissions  (IE: public,protected,private)
     *
     * NOTE: Accessing private properties is possible, but care must be taken
     * if that private property was defined lower into the inheritance chain.
     * For example, if class A extends class B, and class B defines a private
     * property called 'foo', getProperty will throw a ReflectionException.
     *
     * Instead, you can loop over getParentClass until it returns false to
     * look for the private property, at which point you can access and/or
     * modify its value as needed. (modify this method if needed)
     *
     * @param ObjectUtil $object
     * @param string $name The property name
     * @return mixed|null
     * @throws \ReflectionException
     */
    public static function getObjectPropertyValue($object, $name)
    {
        $reflect = new \ReflectionClass($object);
        $property = $reflect->getProperty($name);
        if ($property) {
            $property->setAccessible(true);
            return $property->getValue($object);
        }

        return null;
    }

    /**
     * This method updates a value in an object first looking for a set.... method if that fails then the
     * objects property is set directly ignoring the properties scope (IE: public,protected,private)
     * This is useful for loading an object with data from data sources such as DB or JSON etc...
     *
     * @param mixed $object
     * @param string $name
     * @param mixed $value
     * @return mixed
     * @throws \ReflectionException
     */
    public static function setObjectPropertyValue($object, $name, $value)
    {
        $reflect = new \ReflectionClass($object);
        $property = $reflect->getProperty($name);
        if ($property) {
            $property->setAccessible(true);
            $property->setValue($object, $value);
        }

        return $value;
    }

    
    /**
     * Take a class in the form of Tk_Some_Class
     * And convert it to a class like \Tk\Some\Class
     *
     * @param string $class
     * @return string
     */
    public static function toNamespaceSlash($class)
    {
        if (strpos($class, '\\') != -1 && strpos($class, '_') > -1) {
            $class = '\\'.str_replace('_', '\\', $class);
        }
        return $class;
    }

    /**
     * Take a class in the form of \Tk\Some\Class
     * And convert it to a namespace class like Tk_Some_Class
     *
     * @param string $class
     * @return string
     */
    public static function toNamespaceUnderscore($class)
    {
        if (strpos($class, '_') != -1 && strpos($class, '\\') > -1) {
            $class = str_replace('\\', '_', $class);
            if ($class[0] == '_')
                $class = substr($class, 1);
        }
        return $class;
    }

    /**
     * Get the base classname of an object without the namespace
     * The supplied parameter can be an object or a classname string
     *
     * @param ObjectUtil|string $class Can be an object or a classname string
     * @return bool|int|string
     */
    public static function basename($class)
    {
        if (is_object($class)) $class = get_class($class);
        if ($pos = strrpos($class, '\\')) return substr($class, $pos + 1);
        return $pos;
    }

    /**
     * Get the path of a class
     *
     * @param string|ObjectUtil $class
     * @return string
     * @throws \ReflectionException
     */
    public static function classPath($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }
        $rc = new \ReflectionClass($class);
        return $rc->getFileName();
    }

    /**
     * Get the site relative url path of a class
     *
     * Use \Tk\Uri to get the full URL
     *  - \Tk\Uri::create(ClassTool::classUrl('\Tk\SomeClass', $config->getAppPath));
     *
     * @param string|ObjectUtil $class
     * @param string $sitePath full path to the base of the site
     * @return string
     * @throws \ReflectionException
     */
    public static function classUrl($class, $sitePath)
    {
        $sitePath = rtrim($sitePath, '/');
        if (is_object($class)) {
            $class = get_class($class);
        }
        $rc = new \ReflectionClass($class);
        $path = $rc->getFileName();
        
        if (strpos($path, $sitePath) === 0) {
            return str_replace($sitePath, '' , $path);
        }
        return basename($path);
    }

    /**
     * Get a list of constant name value pairs for a passed class name
     *
     * @param string $class A
     * @param string $prefix If set will only return const values whose name starts with this prefix
     * @return array
     * @throws \ReflectionException
     */
    public static function getClassConstants($class, $prefix = '')
    {
        if (is_object($class)) {
            $class = get_class($class);
        } else if (!class_exists($class)) {
            throw new \InvalidArgumentException('Class Not Found!');
        }
        $oReflect = new \ReflectionClass($class);
        $constList = $oReflect->getConstants();
        if (!$prefix) {
            return $constList;
        }
        $retList = array();
        foreach ($constList as $k => $v) {
            if (substr($k, 0, strlen($prefix)) == $prefix) {
                $retList[$k] = $v;
            }
        }
        return $retList;
    }

    /**
     * Convert a map array to a stdClass object
     *
     * @param array $array
     * @return \stdClass|null Returns null on error
     */
    public static function arrayToObject($array)
    {
        if (!is_array($array)) {
            return null;
        }
        $object = new \stdClass();
        if (is_array($array) && count($array) > 0) {
            foreach ($array as $name => $value) {
                $name = strtolower(trim($name));
                if (!empty($name)) {
                    $object->$name = self::arrayToObject($value);
                }
            }
            return $object;
        }
        return null;
    }

}