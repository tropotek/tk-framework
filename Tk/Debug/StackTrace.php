<?php
namespace Tk\Debug;


/**
 * Class StackTrace
 *
 * @author Michael Mifsud <http://www.tropotek.com/>
 * @see http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
class StackTrace {


    /**
     * Get the backtrace dump as a string
     *
     * @param int $skip
     * @param string $sitePath
     * @return string
     */
    static function getBacktrace($skip = 1, $sitePath = '')
    {
        $stackTraceArray = debug_backtrace();
        for ($i = 0; $i < $skip && $i < count($stackTraceArray); $i++) {
            array_shift($stackTraceArray);
        }
        $str = self::traceToString($stackTraceArray, $sitePath);
        return $str;
    }

    /**
     * Take a stack trace array from \Exception::getTrace or debug_backtrace()
     * and convert it to a string
     *
     * @param array $stackTraceArray
     * @param string $sitePath
     * @return string
     */
    static function traceToString($stackTraceArray, $sitePath = '')
    {
        $str = '';
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
                if ($sitePath) {    // Make the path relative if sitePath exists
                    $file = str_replace($sitePath, '', $file);
                }
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
                        // ToDO: We get a recursive error here for some reason????
                        //$o = print_r($o, true);
                        $o = 'Array['.count($o).']';
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
        return trim($str);
    }

    /**
     * @param int $dumpLine
     * @param bool $showClass
     * @param bool $showFunction
     * @return string
     */
    public static function dumpLine($dumpLine = 1, $showClass = false, $showFunction = false)
    {
        $line = debug_backtrace();
        $line = $line[$dumpLine];

        $class = '';
        if ($showClass && !empty($line['object'])) {
            $class = ': ' . get_class($line['object']);
        }

        if ($showFunction && !empty($line['function'])) {
            $class .= '::' . $line['function'] . '()';

        }

        $path = str_replace(\Tk\Config::getInstance()->getSitePath(), '', $line['file']);
        $str  = sprintf('%s [%s]%s', $path, $line['line'], $class);
        return $str;
    }
}