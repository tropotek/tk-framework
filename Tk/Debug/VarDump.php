<?php
namespace Tk\Debug {

    use Psr\Log\LoggerInterface;
    use Psr\Log\NullLogger;

    /**
     * Class VarDum, used by the vd(), vdd() functions.
     *
     * @author Michael Mifsud <http://www.tropotek.com/>
     * @see http://www.tropotek.com/
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
         * @var LoggerInterface|null
         */
        protected $logger = null;


        /**
         * @param LoggerInterface|null $logger
         * @param string $sitePath
         */
        public function __construct($logger = null, $sitePath = '')
        {
            $this->logger = $logger;
            $this->sitePath = $sitePath;
        }

        /**
         * @param LoggerInterface|null $logger
         * @param string $sitePath
         * @return VarDump
         */
        public static function getInstance($logger = null, $sitePath = '')
        {
            if (static::$instance == null) {
                // TODO: if logger null then create a null logger
                if (!$logger)
                    $logger = new NullLogger();
                if (!$sitePath)
                    $sitePath = dirname(dirname(dirname(dirname(__FILE__))));

                static::$instance = new static($logger, $sitePath);
            }
            return static::$instance;
        }

        /**
         * @return string
         */
        public function getSitePath()
        {
            return $this->sitePath;
        }

        /**
         * @param string $sitePath
         * @return VarDump
         */
        public function setSitePath($sitePath)
        {
            $this->sitePath = $sitePath;
            return $this;
        }

        /**
         * @return LoggerInterface|null
         */
        public function getLogger()
        {
            return $this->logger;
        }

        /**
         * @param LoggerInterface|null $logger
         * @return VarDump
         */
        public function setLogger($logger)
        {
            $this->logger = $logger;
            return $this;
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
            for ($i = 0; $i <= $nest * 2; $i++) $pad .= '  ';

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
                        $a[] = sprintf("%s[%s] => %s\n", $pad, $k, self::varToString($v, $depth, $nest + 1));
                    }
                    $str = sprintf("%s \n%s(\n%s\n%s)", $type, substr($pad, 0, -2), implode('', $a), substr($pad, 0, -2));
                }
            } else if (is_object($var)) {
                $type = '{' . get_class($var) . '} Object';
                if ($nest >= $depth) {
                    $str = $type;
                } else {
                    $a = array();
                    foreach ((array)$var as $k => $v) {
                        $k = str_replace(get_class($var), '*', $k);
                        $a[] = sprintf("%s[%s] => %s", $pad, $k, self::varToString($v, $depth, $nest + 1));
                    }
                    $str = sprintf("%s \n%s{\n%s\n%s}", $type, substr($pad, 0, -2), implode("\n", $a), substr($pad, 0, -2));
                }
            }
            return $str;
        }


    }
}
namespace { // global code
    use Tk\Config;
    use Tk\Debug\VarDump;


    /**
     * logger Helper function
     * Replacement for var_dump();
     *
     * @return string
     */
    function vd() {
        //$vd = VarDump::getInstance();
        $vd = VarDump::getInstance(Config::getInstance()->getLog());
        $line = current(debug_backtrace());
        $path = str_replace($vd->getSitePath(), '', $line['file']);
        $str = '';
        $str .= "\n";
        $str .= $vd->makeDump(func_get_args());
        $str .= sprintf('vd(%s) %s [%s];', implode(', ', $vd->getTypeArray(func_get_args())), $path, $line['line']) . "\n";
        $vd->getLogger()->debug($str);
        return $str;
    }

    /**
     * logger Helper function with stack trace.
     * Replacement for var_dump();
     *
     * @return string
     */
    function vdd() {
        //$vd = VarDump::getInstance();
        $vd = VarDump::getInstance(Config::getInstance()->getLog());
        $line = current(debug_backtrace());
        $path = str_replace($vd->getSitePath(), '', $line['file']);
        $str = '';
        $str .= "\n";
        $str .= $vd->makeDump(func_get_args(), true);
        $str .= sprintf('vdd(%s) %s [%s]', implode(', ', $vd->getTypeArray(func_get_args())), $path, $line['line']) . "\n";
        $vd->getLogger()->debug($str);
        return $str;
    }
}