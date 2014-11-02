<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk;


/**
 * Base class for all Sys Exceptions.
 *
 * @package Tk
 */
class Exception extends \Exception
{

    /**
     * Define an assoc array of error string
     * in reality the only entries we should
     * consider are E_WARNING, E_NOTICE, E_USER_ERROR,
     * E_USER_WARNING and E_USER_NOTICE
     */
    static $errorStr = array('E_ERROR' => 'Error', 'E_WARNING' => 'Warning', 'E_PARSE' => 'Parsing Error',
        'E_NOTICE' => 'Notice', 'E_CORE_ERROR' => 'Core Error', 'E_CORE_WARNING' => 'Core Warning',
        'E_COMPILE_ERROR' => 'Compile Error', 'E_COMPILE_WARNING' => 'Compile Warning', 'E_USER_ERROR' => 'User Error',
        'E_USER_WARNING' => 'User Warning', 'E_USER_NOTICE' => 'User Notice', 'E_STRICT' => 'Runtime Notice',
        'E_RECOVERABLE_ERROR' => 'Catchable Fatal Error', 'E_DEPRECATED' => 'Deprecated Code Warning',
        'E_USER_DEPRECATED' => 'User Deprecated Code Warning');

    static $errorType = array(1 => 'E_ERROR', 2 => 'E_WARNING', 4 => 'E_PARSE', 8 => 'E_NOTICE',
        16 => 'E_CORE_ERROR', 32 => 'E_CORE_WARNING', 64 => 'E_COMPILE_ERROR', 128 => 'E_COMPILE_WARNING',
        256 => 'E_USER_ERROR', 512 => 'E_USER_WARNING', 1024 => 'E_USER_NOTICE', 2048 => 'E_STRICT',
        4096 => 'E_RECOVERABLE_ERROR', 8192 => 'E_DEPRECATED', 16384 => 'E_USER_DEPRECATED');

    private $dump = '';

    /**
     * redefine the constructor, make the message required
     *
     * @param string $message
     * @param int $code
     * @param \Exception $previous
     */
    public function __construct($message, $code = 1, \Exception $previous = null)
    {
        parent::__construct((string)$message, (int)$code, $previous);
    }

    /**
     * Set any memory, code dump data to display in the eception error
     *
     * @param string $dump
     */
    public function setDump($dump)
    {
        $this->dump = $dump;
    }

    /**
     * Redefine if toString()
     *
     * @param bool $showTrace
     * @return string
     */
    public function toString($showTrace = true)
    {
        $str = "\n";
        if ($this->message != null) {
            //$str .= preg_replace('/<a href=\'(\S+)\'>(\S+)<\/a>/', '<a href="http://www.php.net/manual/en/$1.php" target="_blank">$1</a>', $this->message) . "\n";
        }

        $str .= "Message:     " . trim($this->getMessage()) . "\n";
        $str .= "Location:    " . $this->getFile() . " (" . $this->getLine() . ")" . "\n";
        $str .= "Exception:   " . get_class($this) . " [{$this->code}]: \n";
        $str .= "Type:        " . self::$errorType[$this->code] . ' (' . self::$errorStr[self::$errorType[$this->code]] . ")\n";
        $str .= "PHP:         " . PHP_VERSION . ' (' . PHP_OS . ")\n";
        if (isset($_SERVER['REQUEST_URI'])) {
            $str .= "Request URI: " . $_SERVER['REQUEST_URI'] . "\n";
        }
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $str .= "Method:      " . $_SERVER['REQUEST_METHOD'] . "\n";
        }
        if (isset($_SERVER['HTTP_REFERER'])) {
            $str .= "Referrer:    " . $_SERVER['HTTP_REFERER'] . "\n";
        }
        if (isset($_SERVER['SERVER_NAME']) && isset($_SERVER['SERVER_ADDR'])) {
            $str .= "Server:      " . ($_SERVER['SERVER_NAME'] != '' ? $_SERVER['SERVER_NAME'] : $_SERVER['SERVER_ADDR']) . "\n";
        }
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $str .= "Client:      " . $_SERVER['REMOTE_ADDR'] . "\n";
        }
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $str .= "User Agent:  " . $_SERVER['HTTP_USER_AGENT'] . "\n";
        }

        // Retain the try catch, this method is not allowed to throw exceptions..
        // TODO: Try to debug why error
//        try {
//            if (defined('TK_CONFIG_DONE') && Config::getInstance()->getAuth()) {
//                $user = Config::getInstance()->getAuth()->getIdentity();
//                if ($user instanceof \Usr\Db\User) {
//                    $str .= "Username:    " . $user->username . ' <' . $user->email . '> - [ID: '. $user->id . "]\n";
//                }
//                $str .= "\n";
//            }
//        } catch (\Exception $e) {}

        if ($showTrace) {
            if ($this->dump != null) {
                $str .= $this->dump . "\n\n";
            }
            $repPath = dirname(dirname(dirname(__FILE__)));
            $trace = str_replace($repPath, '', $this->getTraceAsString());
            $str .= $trace . "\n\n";
        }

        return $str;
    }

    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Create a browser safe string for the error
     *
     * @param bool $showTrace
     * @return string
     */
    public function toWebString($showTrace = true)
    {
        $str = $this->toString(false);

        if ($showTrace) {
            if ($this->dump != null) {
                $str .= htmlentities($this->dump) . "\n\n";
            }
            $str .= htmlentities($this->getTraceAsString()) . "\n";
        }
        return $str;
    }
}

/**
 * Stops Code execution
 *
 * @package Tk
 */
class FatalException extends Exception
{
}

/**
 * Tk_RuntimeException is the superclass of those Tk_Exceptions that can be thrown
 * during normal operation.
 *
 * @package Tk
 */
class RuntimeException extends FatalException
{
}

/**
 * An Illegal Argument Tk_Exception.
 * Thrown to indicate that a method has been passed an illegal or
 * inappropriate argument.
 *
 * @package Tk
 */
class IllegalArgumentException extends RuntimeException
{
}

/**
 * Thrown to indicate that an index of some sort of variable is out of range.
 *   (Such as to an array, to a string, or to a vector)
 *
 * @package Tk
 */
class IndexOutOfBoundsException extends RuntimeException
{
}

/**
 * Thrown to indicate that an index of some sort of variable is of null.
 *
 * @package Tk
 */
class NullPointerException extends RuntimeException
{
}

/**
 * Thrown to indicate that a method/function/object is deprecated and no longer available.
 * This should only be used to force developers to update outdated code. This should not be used
 * unless you want to stop a system because the deprecated code will cause further system unsuitability.
 *
 * Generally you should ensure your code is backward compatible until the nex major version release, only on
 * that release should you remove functionality and deprecated code.
 *
 * @package Tk
 */
class DeprecatedException extends FatalException
{
}


