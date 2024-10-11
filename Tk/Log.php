<?php
namespace Tk;

use Tk\Logger\Handler;
use Tk\Logger\LoggerInterface;

/**
 * A basic log interface to help with logging through the PSR interface,
 * This should be initiated in the boostrap phase of the request
 *
 * IE:
 *   \Tk\Log::instance($config->getLog());
 */
class Log
{
    /**
     * use this in your query to disable logging for a request
     * Handy for API calls to reduce clutter in a log
     */
    const NO_LOG = 'nolog';

    protected static mixed $_instance = null;

    private Handler $handler;


    public function __construct()
    {
        $this->handler = new Handler();
    }

    public static function instance(): self
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public static function setEnableNoLog(bool $b): void
    {
        self::getHandler()->noLogEnabled = $b;
    }

    public static function addLogger(LoggerInterface $logger): void
    {
        self::getHandler()->addLogger($logger);
    }

    public static function getHandler(): Handler
    {
        return self::instance()->handler;
    }

    /**
     * System is unusable.
     */
    public static function emergency(string $message, array $context = []): void
    {
        self::getHandler()->emergency($message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     */
    public static function alert(string $message, array $context = []): void
    {
        self::getHandler()->alert($message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     */
    public static function critical(string $message, array $context = []): void
    {
        self::getHandler()->critical($message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     */
    public static function error(string $message, array $context = []): void
    {
        self::getHandler()->error($message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     */
    public static function warning(string $message, array $context = []): void
    {
        self::getHandler()->warning($message, $context);
    }

    /**
     * Normal but significant events.
     */
    public static function notice(string $message, array $context = []): void
    {
        self::getHandler()->notice($message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     */
    public static function info(string $message, array $context = []): void
    {
        self::getHandler()->info($message, $context);
    }

    /**
     * Detailed debug information.
     */
    public static function debug(string $message, array $context = []): void
    {
        self::getHandler()->debug($message, $context);
    }

}