<?php
namespace Tk;


/**
 * A basic log interface to help with logging through the PSR interface,
 * This must be initiated in the boostrap phase of the session
 *
 * IE:
 *   \Tk\Log::getInstance($config->getLog());
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2017 Michael Mifsud
 */
class Log
{
    /**
     * @var Log
     */
    protected static $instance = null;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger = null;



    protected function __construct($logger = null)
    {
        if (!$logger) $logger = new \Psr\Log\NullLogger();
        $this->logger = $logger;
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @return Log|static
     */
    public static function getInstance($logger = null)
    {
        if (!self::$instance) {
            self::$instance = new static($logger);
        }
        return self::$instance;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public static function emergency($message, array $context = array())
    {
        $l = self::getInstance()->getLogger();
        $l->emergency($message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public static function alert($message, array $context = array())
    {
        $l = self::getInstance()->getLogger();
        $l->alert($message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public static function critical($message, array $context = array())
    {
        $l = self::getInstance()->getLogger();
        $l->critical($message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public static function error($message, array $context = array())
    {
        $l = self::getInstance()->getLogger();
        $l->error($message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public static function warning($message, array $context = array())
    {
        $l = self::getInstance()->getLogger();
        $l->warning($message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public static function notice($message, array $context = array())
    {
        $l = self::getInstance()->getLogger();
        $l->notice($message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public static function info($message, array $context = array())
    {
        $l = self::getInstance()->getLogger();
        $l->info($message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public static function debug($message, array $context = array())
    {
        $l = self::getInstance()->getLogger();
        $l->debug($message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public static function log($level, $message, array $context = array())
    {
        $l = self::getInstance()->getLogger();
        $l->log($level, $message, $context);
    }
}