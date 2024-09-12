<?php
namespace Tk;

use Tk\Logger\Handler;

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

    private Handler $logger;


    public function __construct()
    {
        $this->logger = new Handler();
    }

    public static function instance(): Log
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new static();
        }
        return self::$_instance;
    }

    public static function addHandler(\Psr\Log\LoggerInterface $logger): void
    {
        self::instance()->getLogger()->addHandler($logger);
    }

    public static function setEnableNoLog(bool $b): void
    {
        self::instance()->getLogger()->noLogEnabled = $b;
    }

    public static function getLogger(): \Psr\Log\LoggerInterface|Handler
    {
        return self::instance()->logger;
    }

    /**
     * System is unusable.
     */
    public static function emergency(string $message, array $context = []): void
    {
        self::instance()->getLogger()->emergency($message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     */
    public static function alert(string $message, array $context = []): void
    {
        self::instance()->getLogger()->alert($message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     */
    public static function critical(string $message, array $context = []): void
    {
        self::instance()->getLogger()->critical($message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     */
    public static function error(string $message, array $context = []): void
    {
        self::instance()->getLogger()->error($message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     */
    public static function warning(string $message, array $context = []): void
    {
        self::instance()->getLogger()->warning($message, $context);
    }

    /**
     * Normal but significant events.
     */
    public static function notice(string $message, array $context = []): void
    {
        self::instance()->getLogger()->notice($message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     */
    public static function info(string $message, array $context = []): void
    {
        self::instance()->getLogger()->info($message, $context);
    }

    /**
     * Detailed debug information.
     */
    public static function debug(string $message, array $context = []): void
    {
        self::instance()->getLogger()->debug($message, $context);
    }

}