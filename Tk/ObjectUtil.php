<?php
namespace Tk;

/**
 * This object is a utility object to perform actions
 * with class names and name-spacing issues.
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
     */
    public static function getPropertyValue(object $object, string $property): mixed
    {
        try {
            $rClass = new \ReflectionClass($object);
            if ($rClass->hasProperty($property)) {
                $rProperty = $rClass->getProperty($property);
                return $rProperty->getValue($object);
            }
            $method = sprintf('get%s', ucfirst($property));
            if ($rClass->hasMethod($method)) {
                $rMethod = $rClass->getMethod($method);
                if (!$rMethod->getNumberOfRequiredParameters()) {
                    return $object->$method();
                }
            }
        } catch (\ReflectionException $e) {
            Log::warning($e->__toString());
        }
        return null;
    }

    /**
     * This is useful for loading an object with data from data sources such as DB or JSON etc...
     */
    public static function setPropertyValue(object $object, string $property, mixed $value = null): mixed
    {
        try {
            $rClass = new \ReflectionClass($object);
            $method = sprintf('set%s', ucfirst($property));
            if ($rClass->hasProperty($property)) {
                $rProperty = $rClass->getProperty($property);
                $rProperty->setValue($object, $value);
            } elseif ($rClass->hasMethod($method)) {
                $rMethod = $rClass->getMethod($method);
                if ($rMethod->getNumberOfRequiredParameters() == 1) {
                    $object->$method($value);
                }
            }
        } catch (\ReflectionException $e) {
            Log::warning($e->__toString());
        }
        return $value;
    }

    /**
     * Get the base classname of an object without the namespace
     * The supplied parameter can be an object or a classname string
     */
    public static function basename(object|string $class): bool|string
    {
        if (is_object($class)) $class = get_class($class);
        if ($pos = strrpos($class, '\\')) return substr($class, $pos + 1);
        return false;
    }

    /**
     * Return the namespace for this object
     */
    public static function getBaseNamespace(object|string $class): string
    {
        if (is_object($class)) $class = get_class($class);
        $list = explode('\\', $class);
        return current($list);
    }

    /**
     * Get the file path of a class
     */
    public static function classPath(object|string $class): string
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
     */
    public static function classUses(object|string $obj, string $trait): bool
    {
        $arr = class_uses($obj);
        vd($arr, $trait);
        foreach ($arr as $v) {
            if ($v == $trait) return true;
        }
        return false;
    }

    /**
     * Get a list of constant name value pairs for a passed class name
     */
    public static function getClassConstants(object|string $class, string $prefix = '', bool $autoName = false): array
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
                if (str_starts_with($k, $prefix)) {
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
     */
    public static function arrayToObject(array $array): ?\stdClass
    {
        $object = new \stdClass();
        if (count($array) > 0) {
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