<?php
namespace Tk\Debug {

    use Psr\Log\LoggerInterface;
    use Psr\Log\NullLogger;
    use Tk\Traits\SingletonTrait;

    /**
     * Class VarDum, used by the vd(), vdd() functions.
     *
     * @author Tropotek <http://www.tropotek.com/>
     */
    class VarDump
    {
        use SingletonTrait;

        protected string $basePath = '';

        protected ?LoggerInterface $logger = null;


        public function __construct(?LoggerInterface $logger = null, string $basePath = '')
        {
            $this->logger = $logger;
            $this->basePath = $basePath;
        }

        public static function instance(?LoggerInterface $logger = null, string $basePath = ''): VarDump
        {
            if (static::$_INSTANCE == null) {
                if (!$logger)
                    $logger = new NullLogger();
                if (!$basePath)
                    $basePath = dirname(__DIR__, 3);

                static::$_INSTANCE = new static($logger, $basePath);
            }
            return static::$_INSTANCE;
        }

        public function getBasePath(): string
        {
            return $this->basePath;
        }

        public function setBasePath(string $basePath): VarDump
        {
            $this->basePath = $basePath;
            return $this;
        }

        public function getLogger(): ?LoggerInterface
        {
            return $this->logger;
        }

        public function setLogger(?LoggerInterface $logger): VarDump
        {
            $this->logger = $logger;
            return $this;
        }

        public function makeDump(array $args, bool $showTrace = false): string
        {
            $str = $this->argsToString($args);
            if ($showTrace) {
                $str .= "\n" . StackTrace::getBacktrace(4, $this->basePath) . "\n";
            }
            return $str;
        }

        public function argsToString(array $args): string
        {
            $output = '';
            foreach ($args as $var) {
                $output .= self::varToString($var) . "\n";
            }
            return $output;
        }

        /**
         * return the types of the argument array
         */
        public function getTypeArray(array $args): array
        {
            $arr = [];
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
         */
        public static function varToString($var, int $depth = 5, int $nest = 0): string
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
                $a = [];
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
                    $a = [];
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
    use Tk\Debug\VarDump;
    use Tk\Factory;


    /**
     * logger Helper function
     * Replacement for var_dump();
     */
    function vd(): string
    {
        $vd = VarDump::instance(Factory::instance()->getLogger());
        $line = current(debug_backtrace());
        $path = str_replace($vd->getBasePath(), '', $line['file']);
        $str = "\n";
        $str .= $vd->makeDump(func_get_args());
        $str .= sprintf('vd(%s) %s [%s];', implode(', ', $vd->getTypeArray(func_get_args())), $path, $line['line']) . "\n";
        $vd->getLogger()->debug($str);
        return $str;
    }

    /**
     * logger Helper function with stack trace.
     * Replacement for var_dump();
     */
    function vdd(): string
    {
        $vd = VarDump::instance(Factory::instance()->getLogger());
        $line = current(debug_backtrace());
        $path = str_replace($vd->getBasePath(), '', $line['file']);
        $str = "\n";
        $str .= $vd->makeDump(func_get_args(), true);
        $str .= sprintf('vdd(%s) %s [%s]', implode(', ', $vd->getTypeArray(func_get_args())), $path, $line['line']) . "\n";
        $vd->getLogger()->debug($str);
        return $str;
    }
}