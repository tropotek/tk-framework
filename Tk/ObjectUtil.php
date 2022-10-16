<?php
namespace Tk;

/**
 * This object is a utility object to perform actions 
 * with class names and name-spacing issues.
 *
 *
 * @author Tropotek <http://www.tropotek.com/>
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
     */
    public static function objectPropertyExists(object $object, string $name): bool
    {
        try {
            $reflect = new \ReflectionClass($object);
            return $reflect->hasProperty($name);
        } catch (\Exception $e) {}
        return false;
    }

    /**
     * NOTE: Accessing private properties is possible, but care must be taken
     * if that private property was defined lower into the inheritance chain.
     * For example, if class A extends class B, and class B defines a private
     * property called 'foo', getProperty will throw a ReflectionException.
     *
     * Instead, you can loop over getParentClass until it returns false to
     * look for the private property, at which point you can access and/or
     * modify its value as needed. (modify this method if needed)
     *
     * @return mixed|null
     */
    public static function getObjectPropertyValue(object $object, string $name)
    {
        try {
            $reflect = new \ReflectionClass($object);
            $property = $reflect->getProperty($name);
            if ($property) {
                $property->setAccessible(true);
                return $property->getValue($object);
            }
        } catch (\ReflectionException $e) {
            Log::warning($e->__toString());
        }
        return null;
    }

    /**
     * This is useful for loading an object with data from data sources such as DB or JSON etc...
     *
     * @param mixed|null $value
     * @return mixed
     */
    public static function setPropertyValue(object $object, string $name, $value = null)
    {
        try {
            $reflect = new \ReflectionClass($object);
            $property = $reflect->getProperty($name);

            if ($property) {
                if (!$property->isPublic())
                    $property->setAccessible(true);
                $property->setValue($object, $value);
            } else {
                Log::warning('TODO: Do we want to set a dynamic object property here???');
            }
        } catch (\ReflectionException $e) {
            Log::warning($e->__toString());
        }
        return $value;
    }

    /**
     * Get the base classname of an object without the namespace
     * The supplied parameter can be an object or a classname string
     *
     * @param object|string $class Can be an object or a classname string
     * @return bool|string The base classname or false on failure.
     */
    public static function basename($class)
    {
        if (is_object($class)) $class = get_class($class);
        if ($pos = strrpos($class, '\\')) return substr($class, $pos + 1);
        return false;
    }

    /**
     * Return the namespace for this object
     *
     * @param string|object $class
     */
    public static function getBaseNamespace($class): string
    {
        if (is_object($class)) $class = get_class($class);
        $list = explode('\\', $class);
        return current($list);
    }

    /**
     * Get the file path of a class
     *
     * @param string|object $class
     */
    public static function classPath($class): string
    {
        if (is_object($class)) $class = get_class($class);

        $file = '';
        try {
            $rc = new \ReflectionClass($class);
            $file = $rc->getFileName();
        } catch (\ReflectionException $e) {
            Log::warning($e->__toString());
        }
        return $file;
    }

    /**
     * Return true if a class uses the given trait
     *
     * @param object|string $obj An object (class instance) or a string (class name).
     * @param string $trait A trait class name
     * @return bool
     */
    public static function classUses($obj, string $trait): bool
    {
        $arr = class_uses($obj);
        foreach ($arr as $v) {
            if ($v == $trait) return true;
        }
        return false;
    }

    /**
     * Get a list of constant name value pairs for a passed class name
     *
     * @param string|object $class
     * @param string $prefix If set will only return const values whose name starts with this prefix
     * @param bool $autoName Not be sure that there are no duplicate constant values if this option is true
     * @return array
     */
    public static function getClassConstants($class, string $prefix = '', bool $autoName = false)
    {
        if (is_object($class)) {
            $class = get_class($class);
        } else if (!class_exists($class)) {
            return [];
        }
        $retList = [];
        try {
            $oReflect = new \ReflectionClass($class);
            $constList = $oReflect->getConstants();
            if (!$prefix) {
                return $constList;
            }
            foreach ($constList as $k => $v) {
                if (substr($k, 0, strlen($prefix)) == $prefix) {
                    if ($autoName) {
                        $k = ucwords(preg_replace('/[A-Z]/', ' $0', $v));
                    }
                    $retList[$k] = $v;
                }
            }
        } catch (\ReflectionException $e) {
            Log::warning($e->__toString());
        }
        return $retList;
    }

    /**
     * Convert a map array to a stdClass object
     *
     * @param array $array
     */
    public static function arrayToObject(array $array): ?\stdClass
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