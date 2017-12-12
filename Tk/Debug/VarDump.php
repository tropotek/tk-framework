<?php
namespace Tk\Debug;

/**
 * Class VarDum, used by the vd(), vdd() functions.
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class VarDump
{
    /**
     * @var VarDump
     */
    public static $instance = null;

    /**
     * @var string
     */
    protected $sitePath = '';

    
    /**
     * constructor.
     *
     * @param string $sitePath
     */
    public function __construct($sitePath = '')
    {
        $this->sitePath = $sitePath;
    }

    /**
     * @param string $sitePath
     * @return VarDump
     */
    public static function getInstance($sitePath = '')
    {
        if (static::$instance == null) {
            static::$instance = new static($sitePath);
        }
        return static::$instance;
    }

    /**
     * 
     * @param array $args
     * @param bool $showTrace
     * @return string
     */
    public function makeDump($args, $showTrace = false)
    {
        $str = $this->argsToString($args);
        if ($showTrace) {
            // TODO: make the depth value configurable?????
            $str .= "\n" . StackTrace::getBacktrace(4, $this->sitePath) . "\n";
        }
        return $str;
    }

    /**
     * @param array $args
     * @return string
     */
    public function argsToString($args)
    {
        $output = '';
        foreach ($args as $var) {
            $output .= self::varToString($var) . "\n";
        }
        return $output;
    }

    /**
     * return the types of the argument array
     *
     * @param $args
     * @return array
     */
    public function getTypeArray($args)
    {
        $arr = array();
        foreach ($args as $a) {
            $type = gettype($a);
            if ($type == 'object')
                $type = get_class($a);
            $arr[] = $type;
        }
        return $arr;
    }


    /**
     * return a var dump string from an array of arguments
     *
     * @param mixed $var
     * @param int $depth
     * @param int $nest
     * @return string
     */
    public static function varToString($var, $depth = 5, $nest = 0)
    {
        $pad = '';
        for($i=0;$i<=$nest*2;$i++) $pad .= '  ';
        
        $type = 'native';
        $str = $var;
        
        if ($var === null) {
            $str = 'NULL';
        } else if (is_bool($var)) {
            $type = 'Boolean';
            $str = $var == true ? 'true' : 'false';
        } else if (is_string($var)) {
            $type = 'String';
            $str = str_replace("\0", '|', $var);
        } else if (is_resource($var)) {
            $type = 'Resource';
            $str = get_resource_type($var);
        } else if (is_array($var)) {
            $type = sprintf('Array[%s]', count($var));
            $a = array();
            if ($nest >= $depth) {
                $str = $type;
            } else {
                foreach ($var as $k => $v) {
                    $a[] = sprintf("%s[%s] => %s\n",$pad, $k, self::varToString($v, $depth, $nest + 1));
                }
                $str = sprintf("%s \n%s(\n%s\n%s)", $type, substr($pad,0,-2), implode('', $a), substr($pad,0,-2));
            }
        } else if (is_object($var)) {
            $type = '{' . get_class($var) . '} Object';
            if ($nest >= $depth) {
                $str = $type;
            } else {
                $a = array();
                foreach ((array)$var as $k => $v) {
                    $k = str_replace(get_class($var), '*', $k);
                    $a[] = sprintf("%s[%s] => %s", $pad, $k, self::varToString($v, $depth, $nest+1));
                }
                $str = sprintf("%s \n%s{\n%s\n%s}", $type, substr($pad,0,-2), implode("\n", $a), substr($pad,0,-2));
            }
        }
        return $str;
    }


}
