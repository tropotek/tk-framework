<?php
namespace Tk;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Tk\Traits\SingletonTrait;

/**
 * A basic log interface to help with logging through the PSR interface,
 * This should be initiated in the boostrap phase of the request
 *
 * IE:
 *   \Tk\Log::instance($config->getLog());
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class Log
{
    use SingletonTrait;

    /**
     * use this in your query to disable logging for a request
     * Handy for API calls to reduce clutter in a log
     */
    const NO_LOG = 'nolog';

    private LoggerInterface $logger;


    protected function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function instance(?LoggerInterface $logger = null): Log
    {
        if (!self::$_INSTANCE && $logger) {
            self::$_INSTANCE = new static($logger ?? new \Symfony\Component\HttpKernel\Log\Logger());
        }
        return self::$_INSTANCE;
    }

    /**
     * Logs with an arbitrary level.
     */
    public static function log(string $level, string $message, array $context = [])
    {
        $l = self::instance()->getLogger();
        $l->log($level, self::getCallerLine(2) . $message, $context);
    }

    private static function getCallerLine(int $shift = 2): string
    {
        $bt = debug_backtrace();
        for($i = 0; $i < $shift; $i++) array_shift($bt);
        $caller = array_shift($bt);
        $str = '';
        if ($caller) {
            $config = Config::instance();
            $line = $caller['line'];
            $file = str_replace($config->getBasePath(), '', $caller['file']);
            $str = sprintf('[%s:%s] ', $file, $line);
        }
        return $str;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }


    /**
     * System is unusable.
     */
    public static function emergency(string $message, array $context = [])
    {
        self::log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     */
    public static function alert(string $message, array $context = [])
    {
        self::log(LogLevel::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     */
    public static function critical(string $message, array $context = [])
    {
        self::log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     */
    public static function error(string $message, array $context = [])
    {
        self::log(LogLevel::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     */
    public static function warning(string $message, array $context = [])
    {
        self::log(LogLevel::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     */
    public static function notice(string $message, array $context = [])
    {
        self::log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     */
    public static function info(string $message, array $context = [])
    {
        self::log(LogLevel::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     */
    public static function debug(string $message, array $context = [])
    {
        self::log(LogLevel::DEBUG, $message, $context);
    }

}