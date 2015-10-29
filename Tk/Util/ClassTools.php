<?php
namespace Tk\Util;


class ClassTools
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
    static function classpath($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }
        $rc = new \ReflectionClass($class);
        return $rc->getFileName();
    }

    /**
     * Get the url path of a class
     *
     * @param string|Object $class
     * @return string
     */
    static function classUrl($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }
        $rc = new \ReflectionClass($class);
        $path = $rc->getFileName();
        if (strpos($path, \Tk\Config::getInstance()->getAppPath()) === 0) {
            return str_replace(\Tk\Config::getInstance()->getAppPath(), '' , $path);
        }
        return basename($path);
    }



}