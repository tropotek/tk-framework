<?php
namespace Tk;
use Psr\Log\LoggerInterface;

/**
 * Class ErrorHandler
 * 
 * To set this up just call getInstance at the earliest possible convenience.
 * 
 * \Tk\ErrorHandler::getInstance($logger);
 * 
 * NOTICE: for startup errors and errors produced before this object is initialised 
 * see the php system log file if your php.ini is setup for it.
 * 
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class ErrorHandler
{

    /**
     * @var ErrorHandler
     */
    public static $instance = null;

    /**
     * @var LoggerInterface
     */
    protected $log = null;


    /**
     * constructor.
     *
     * @param LoggerInterface $log
     */
    public function __construct(LoggerInterface $log = null)
    {
        $this->log = $log;
        set_error_handler(array($this, 'errorHandler'));
    }

    /**
     * @param LoggerInterface $log
     * @return ErrorHandler
     */
    public static function getInstance(LoggerInterface $log = null)
    {
        if (static::$instance == null) {
            static::$instance = new static($log);
        }
        return static::$instance;
    }


    /**
     * A custom Exception thrower to turn PHP errors into exceptions.
     *
     * @see http://au.php.net/manual/en/class.errorException.php
     *
     * @param string $errno
     * @param string $errstr
     * @param string $errfile
     * @param string $errline
     * @param array $errcontext
     * @return bool
     * @throws Exception
     */
    public function errorHandler($errno, $errstr, $errfile, $errline, $errcontext = array())
    {
//        if (!(error_reporting() & $errno)) {  // Check the PHP ini for the error setting
//            return false;
//        }
        $e = null;
        switch($errno)
        {
            case E_ERROR:               $e = new Exception                 ($errstr, $errno); break;
            case E_WARNING:             $e = new WarningException          ($errstr, $errno); break;
            case E_PARSE:               $e = new ParseException            ($errstr, $errno); break;
            case E_NOTICE:              $e = new NoticeException           ($errstr, $errno); break;
            case E_CORE_ERROR:          $e = new CoreErrorException        ($errstr, $errno); break;
            case E_CORE_WARNING:        $e = new CoreWarningException      ($errstr, $errno); break;
            case E_COMPILE_ERROR:       $e = new CompileErrorException     ($errstr, $errno); break;
            case E_COMPILE_WARNING:     $e = new CoreWarningException      ($errstr, $errno); break;
            case E_USER_ERROR:          $e = new UserErrorException        ($errstr, $errno); break;
            case E_USER_WARNING:        $e = new UserWarningException      ($errstr, $errno); break;
            case E_USER_NOTICE:         $e = new UserNoticeException       ($errstr, $errno); break;
            case E_STRICT:              $e = new StrictException           ($errstr, $errno); break;
            case E_RECOVERABLE_ERROR:   $e = new RecoverableErrorException ($errstr, $errno); break;
            case E_DEPRECATED:          $e = new DeprecatedException       ($errstr, $errno); break;
            case E_USER_DEPRECATED:     $e = new UserDeprecatedException   ($errstr, $errno); break;
            default: $e = new Exception($errstr, $errno);
        }

        if ($errno == E_DEPRECATED || $errno == E_USER_DEPRECATED || $errno == E_RECOVERABLE_ERROR || $errno == E_WARNING || $errno == E_NOTICE) {
            // Log the error in debug modes
            //if (\Tk\Config::getInstance()->isDebug()) {
                if ($this->log) {
                    //$this->log->warning($e->__toString(), $errcontext);
                    $this->log->warning($e->__toString());
                } else {
                    error_log($e->__toString()."\n");
                }
            //}
            return false;
        }
        throw $e;
    }
    
    
}



