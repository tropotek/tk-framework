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
    public static function create($src)
    {
        $e = new static($src->message, $src->code);
        $e->file = $src->file;
        $e->line = $src->line;
        return $e;
    }
    

    /**
     * Set any memory, code dump data to display in the exception error
     *
     * @param string $dump
     */
    public function setDump($dump)
    {
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

