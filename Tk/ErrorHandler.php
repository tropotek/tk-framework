<?php
namespace Tk;

/**
 * To set this up just call ErrorHandler::instance($logger) at the earliest possible convenience.
 *
 * NOTICE: for startup errors and errors produced before this object is initialized
 * see the php system log file if your php.ini is set up for it.
 */
class ErrorHandler
{
    protected static mixed $_instance = null;

    public function __construct()
    {
        set_error_handler([$this, 'errorHandler']);
    }

    public static function instance(): self
    {
        if (is_null(static::$_instance)) {
            static::$_instance = new self();
        }
        return static::$_instance;
    }

    /**
     * A custom Exception thrower to turn PHP errors into exceptions.
     */
    public function errorHandler(int $errno, string $errstr, string $errfile, int $errline, array $errcontext = []): bool
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
            Log::warning($msg);
            return true;
        }

        throw $e;
    }

}
