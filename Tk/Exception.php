<?php
namespace Tk;

/**
 * Class Exception
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
class Exception extends \Exception
{

    /**
     * @var string
     */
    protected $dump = '';

    /**
     * @param \Exception $src
     * @return static
     */
//    public static function createCopy($src, $dump = null)
//    {
//        $e = new static($src->message, $src->code);
//        $e->file = $src->file;
//        $e->line = $src->line;
//        if ($dump) {
//            $e->dump = $dump;
//        }
//        return $e;
//    }

    /**
     * Construct the exception. Note: The message is NOT binary safe.
     * @link http://php.net/manual/en/exception.construct.php
     * @param string $message [optional] The Exception message to throw.
     * @param int $code [optional] The Exception code.
     * @param \Throwable $previous [optional] The previous throwable used for the exception chaining.
     * @param string $dump
     * @since 5.1.0
     */
    public function __construct($message = "", $code = 0, \Throwable $previous = null, $dump = '')
    {
        parent::__construct($message, $code, $previous);
        $this->dump = $dump;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of the exception
     * @link http://php.net/manual/en/exception.tostring.php
     * @return string the string representation of the exception.
     */
    public function __toString()
    {
        $str = parent::__toString();
        if ($this->dump != null) {
            $str .= $this->dump . "\n\n";
        }
        return $str;
    }

}

class WarningException              extends Exception {}
class ParseException                extends Exception {}
class NoticeException               extends Exception {}
class CoreErrorException            extends Exception {}
class CoreWarningException          extends Exception {}
class CompileErrorException         extends Exception {}
class CompileWarningException       extends Exception {}
class UserErrorException            extends Exception {}
class UserWarningException          extends Exception {}
class UserNoticeException           extends Exception {}
class StrictException               extends Exception {}
class RecoverableErrorException     extends Exception {}
class DeprecatedException           extends Exception {}
class UserDeprecatedException       extends Exception {}

