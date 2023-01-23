<?php
namespace Tk;

use Psr\Log\LoggerInterface;
use Tk\Traits\SingletonTrait;

/**
 * To set this up just call ErrorHandler::instance($logger) at the earliest possible convenience.
 *
 * NOTICE: for startup errors and errors produced before this object is initialised 
 * see the php system log file if your php.ini is set up for it.
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class ErrorHandler
{
    use SingletonTrait;

    protected ?LoggerInterface $log = null;


    public function __construct(LoggerInterface $log = null)
    {
        $this->log = $log;
        set_error_handler([$this, 'errorHandler']);
    }

    public static function instance(LoggerInterface $log = null): ErrorHandler
    {
        if (static::$_INSTANCE == null) {
            static::$_INSTANCE = new static($log);
        }
        return static::$_INSTANCE;
    }

    /**
     * A custom Exception thrower to turn PHP errors into exceptions.
     *
     * @throws Exception
     */
    public function errorHandler(string $errno, string $errstr, string $errfile, string $errline, array $errcontext = []): bool
    {
        $e = null;
        switch($errno)
        {
            case E_ERROR:               $e = new Exception                 ($errstr, $errno); break;
            case E_WARNING:             $e = new WarningException          ($errstr, $errno); break;
            case E_PARSE:               $e = new ParseException            ($errstr, $errno); break;
            case E_NOTICE:              $e = new NoticeException           ($errstr, $errno); break;
            case E_CORE_ERROR:          $e = new CoreErrorException        ($errstr, $errno); break;
            case E_COMPILE_WARNING:     $e = new CompileWarningException   ($errstr, $errno); break;
            case E_CORE_WARNING:        $e = new CoreWarningException      ($errstr, $errno); break;
            case E_COMPILE_ERROR:       $e = new CompileErrorException     ($errstr, $errno); break;
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
            $msg = trim($e->getMessage()) . ' in ' . $errfile . ' on line ' . $errline;
            if ($this->log) {
                $this->log->warning($msg);
            } else {
                error_log($msg . "\n");
            }
            return true;
        }

        throw $e;
    }

}



