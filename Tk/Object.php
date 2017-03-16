<?php
namespace Tk;

/**
 * Class ClassTool
 * This object is a utility object to perform actions 
 * with class names and name-spacing issues.
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Object
{
    
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
     * Get the path of a class
     *
     * @param string|Object $class
     * @return string
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
     * @param string|Object $class
     * @param string $sitePath full path to the base of the site
     * @return string
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
     * @throws \InvalidArgumentException
     * @return array
     */
    public static function getClassConstants($class, $prefix = '')
    {
        if (!class_exists($class)) {
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