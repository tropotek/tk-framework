<?php
namespace Tk\Util;

/**
 * Class ClassTool
 * This object is a utility object to perform actions 
 * with class names and name-spacing issues.
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class ClassTool
{


    /**
     * Take a class in the form of Tk_Some_Class
     * And convert it to a class like \Tk\Some\Class
     *
     * @param string $class
     * @return string
     */
    static function toNamespaceSlash($class)
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
    static function toNamespaceUnderscore($class)
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
    static function classPath($class)
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
    static function classUrl($class, $sitePath)
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



}