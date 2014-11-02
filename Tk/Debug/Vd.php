<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Debug;

/**
 * Helper object for the vd() function
 *
 * @package Tk\Debug
 */
class Vd
{
    const TK_NO_TRACE = '_-NO_TRACE-_';

    /**
     * Quick debugging of any variable.
     * Any number of parameters can be sent.
     * Used by the debug function vd()
     *
     * @param mixed args[]
     * @return string
     */
    static function dbgWrite()
    {
        $output = '';
        $args = func_get_args();
        foreach ($args as $var) {
            if ($var === self::TK_NO_TRACE) continue;

            $objStr = $var;
            if ($var === null) {
                $objStr = 'NULL';
            } else if (is_bool($var)) {
                $objStr = $var == true ? 'true' : 'false';
            } else if (is_string($var)) {
                $objStr = str_replace("\0", '|', $var);
//            } else if (is_object($var) && method_exists($var, '__toString')) {
//                $objStr = $var->__toString();
            } else if (is_object($var) || is_array($var)) {
                $objStr = str_replace("\0", '|', print_r($var, true));
            }
            $type = gettype($var);
            if ($type == 'object') {
                $type = get_class($var);
            }
            $output .= "\nvd({" . $type . "}): " . $objStr . "";
        }

        if (in_array(self::TK_NO_TRACE, $args, true)) {
            $msg = $output . "\n";
        } else {
            $msg = $output ."\n\n". self::getTrace(4) . "\n";
        }
        $args = null;

        if (class_exists('\Tk\Log\Log')) {
            return \Tk\Config::getInstance()->getLog()->write($msg, \Tk\Log\Log::DEBUG);
        } else {
            if (substr(php_sapi_name(), 0, 3) == 'cli') {
                echo "\n" . $msg . "\n\n";
            }
            error_log($msg);
        }
    }



    /**
     * Get the debug backtrace as a string
     *
     * @param int $skip (Optional) Default 2
     * @return string
     */
    static function getTrace($skip = 2)
    {
        $trace = debug_backtrace();

        $str = '';
        for ($i = 1; $i < $skip; $i++) {
            array_shift($trace);
        }
        foreach ($trace as $i => $t) {
            $type = '';
            if (isset($t['type'])) {
                $type = $t['type'];
            }
            $class = '';
            if (isset($t['class'])) {
                $class = $t['class'];
            }
            $file = '';
            if (isset($t['file'])) {
                $file = $t['file'];
                // try to get relative path to the site
                if (\Tk\Config::getInstance()->getSitePath())
                    $file = str_replace(\Tk\Config::getInstance()->getSitePath(), '', $file);
            }
            $line = '';
            if (isset($t['line'])) {
                $line = $t['line'];
            }
            $function = '';
            if (isset($t['function'])) {
                $function = $t['function'];
            }
            $args = '()';
            $astr = '';
            if (isset($t['args'])) {
                foreach ($t['args'] as $o) {
                    if (is_object($o)) {
                        $o = get_class($o);
                    }
                    if (is_array($o)) {
                        $o = print_r($o, true);
                    }
                    if (is_string($o) || $o == '') $o = enquote (str_replace(array("\n", "\r"), ' ', substr($o, 0, 32)));
                    $astr .= $o . ', ';
                }
            }
            if ($astr) {
                $args = '(' . substr($astr, 0, -2) . ')';
            }

            $str .= sprintf("[%s] %s(%s): %s%s%s%s \n", $i, $file, $line, $class, $type, $function, $args);
        }
        return $str;

    }
}