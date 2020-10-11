<?php
namespace Tk;

/**
 * Class ObjectUtil
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
     * @param object $object
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
     * NOTE: Accessing private properties is possible, but care must be taken
     * if that private property was defined lower into the inheritance chain.
     * For example, if class A extends class B, and class B defines a private
     * property called 'foo', getProperty will throw a ReflectionException.
     *
     * Instead, you can loop over getParentClass until it returns false to
     * look for the private property, at which point you can access and/or
     * modify its value as needed. (modify this method if needed)
     *
     * @param object $object
     * @param string $name The property name
     * @return mixed|null
     */
    public static function getObjectPropertyValue($object, $name)
    {
        try {
            $reflect = new \ReflectionClass($object);
            $property = $reflect->getProperty($name);
            if ($property) {
                $property->setAccessible(true);
                return $property->getValue($object);
            }

        } catch (\ReflectionException $e) {
            \Tk\Log::warning($e->__toString());
        }
        return null;
    }

    /**
     * This is useful for loading an object with data from data sources such as DB or JSON etc...
     *
     * @param object $object
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public static function setObjectPropertyValue($object, $name, $value)
    {
        try {
            $reflect = new \ReflectionClass($object);
            $property = $reflect->getProperty($name);

            if ($property) {
                if (!$property->isPublic())
                    $property->setAccessible(true);
                $property->setValue($object, $value);
            } else {
                \Tk\Log::warning('TODO: Do we set an objects props here???');
            }
        } catch (\ReflectionException $e) {
            \Tk\Log::warning($e->__toString());
        }
        return $value;
    }

    /**
     * Take a class in the form of Tk_Some_Class
     * And convert it to a class like \Tk\Some\Class
     *
     * @param string|object $class
     * @return string
     */
    public static function toNamespaceSlash($class)
    {
        if (is_object($class)) $class = get_class($class);
        if (strpos($class, '\\') != -1 && strpos($class, '_') > -1) {
            $class = '\\'.str_replace('_', '\\', $class);
        }
        return $class;
    }

    /**
     * Take a class in the form of \Tk\Some\Class
     * And convert it to a namespace class like Tk_Some_Class
     *
     * @param string|object $class
     * @return string
     */
    public static function toNamespaceUnderscore($class)
    {
        if (is_object($class)) $class = get_class($class);
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
     * @param object|string $class Can be an object or a classname string
     * @return bool|int|string
     */
    public static function basename($class)
    {
        if (is_object($class)) $class = get_class($class);
        if ($pos = strrpos($class, '\\')) return substr($class, $pos + 1);
        return $pos;
    }

    /**
     * Return the Base namespace for this object
     *
     * @param string|object $class
     * @return string
     */
    public static function getBaseNamespace($class)
    {
        if (is_object($class)) $class = get_class($class);
        $list = explode('\\', $class);
        return current($list);
    }

    /**
     * Get the path of a class
     *
     * @param string|object $class
     * @return string
     */
    public static function classPath($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }
        $file = '';
        try {
            $rc = new \ReflectionClass($class);
            $file = $rc->getFileName();
        } catch (\ReflectionException $e) {
            \Tk\Log::warning($e->__toString());
        }
        return $file;
    }

    /**
     * Get the site relative url path of a class
     *
     * Use \Tk\Uri to get the full URL
     *  - \Tk\Uri::create(ClassTool::classUrl('\Tk\SomeClass', $config->getAppPath));
     *
     * @param string|object $class
     * @param string $sitePath full path to the base of the site
     * @return string
     */
    public static function classUrl($class, $sitePath)
    {
        $sitePath = rtrim($sitePath, '/');
        if (is_object($class)) {
            $class = get_class($class);
        }
        $path = $sitePath;
        try {
            $rc = new \ReflectionClass($class);
            $path = $rc->getFileName();
            if (strpos($path, $sitePath) === 0) {
                return str_replace($sitePath, '' , $path);
            }
        } catch (\ReflectionException $e) {
            \Tk\Log::warning($e->__toString());
        }
        return basename($path);
    }

    /**
     * Return the classname of an object or return the given parameter
     *
     * @param string|object $obj
     * @return mixed|string
     */
    public static function getClass($obj)
    {
        if (is_object($obj))
            return get_class($obj);
        return $obj;
    }

    /**
     * Return true if a class uses the given trail
     *
     * @param object|string $obj An object (class instance) or a string (class name).
     * @param string $trait
     * @return bool
     */
    public static function classUses($obj, $trait)
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
     * @param string|object $class A
     * @param string $prefix If set will only return const values whose name starts with this prefix
     * @param bool $autoName Not be sure that there are no duplicate constant values if this option is true
     * @return array
     */
    public static function getClassConstants($class, $prefix = '', $autoName = false)
    {
        if (is_object($class)) {
            $class = get_class($class);
        } else if (!class_exists($class)) {
            \Tk\Log::warning('Class Not Found: ' . $class);
            return array();
        }
        $retList = array();
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
            \Tk\Log::warning($e->__toString());
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