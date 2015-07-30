<?php
namespace Tk\Utils;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class StackTrace
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
class StackTrace {


    /**
     * Take a stack trace array from \Exception::getTrace or debug_backtrace()
     * and convert it to a string
     *
     * @param array $stackTraceArray
     * @param int $skip
     * @return string
     */
    static function traceToString($stackTraceArray, $skip = 0)
    {
        $request = Request::createFromGlobals();
        $str = '';
        for ($i = 0; $i < $skip; $i++) {
            array_shift($stackTraceArray);
        }
        foreach ($stackTraceArray as $i => $t) {
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
                if ($request->getBasePath())
                    $file = str_replace($request->getBasePath(), '', $file);
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
                    if (is_string($o) || $o == '') $o = "'" . str_replace(array("\n", "\r"), ' ', substr($o, 0, 32)) . "'";
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


    /**
     * Take a stack trace array from \Exception::getTrace or debug_backtrace()
     * and convert it to a string
     *
     * @param array $stackTraceArray
     * @param int $skip
     * @return string
     *
     */
    static function traceToHtml($stackTraceArray, $skip = 0)
    {
        return '<pre>' . self::traceToString($stackTraceArray, $skip) . '</pre>';
    }


}