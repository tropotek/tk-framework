<?php
/**
 * Created by PhpStorm.
 * User: mifsudm
 * Date: 6/18/15
 * Time: 7:34 AM
 */

namespace Tk;

use \Psr\Log\LoggerInterface;


/**
 *
 *
 *
 *
 * <code>
 * <?php
 * ...
 *
 * $log = null;
 * if (is_file($config->getSystemLogPath())) {
 *   $log = new Logger('system');
 *   $handler = new StreamHandler($config->getSystemLogPath(), $config->getSystemLogLevel());
 *   $formatter = new LineFormatter(null, null, true, true);
 *   $formatter->allowInlineLineBreaks();
 *   $handler->setFormatter($formatter);
 *   $log->pushHandler($handler);
 * }
 * \Tk\Log::getInstance($log);
 *
 * ...
 * ?>
 * </code>
 *
 *
 * Class Log
 * @package Tk
 */
final class Log {


    /**
     * @var Log
     */
    static $instance = null;

    /**
     * @var LoggerInterface
     */
    private $logger = null;

    /**
     * Get an instance of this object
     *
     * @param LoggerInterface $logger
     * @return Log
     */
    static function getInstance(LoggerInterface $logger = null)
    {
        if (self::$instance == null) {
            self::$instance = new self($logger);
        }
        return self::$instance;
    }

    /**
     * Get the logger object
     *
     * @return LoggerInterface|\Psr\Log\NullLogger
     */
    function getLogger()
    {
        return $this->logger;
    }

    /**
     *
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        if (!$this->logger) {
            $this->logger = new \Psr\Log\NullLogger();
        }
    }


    /**
     * Adds a log record at the DEBUG level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public static function d($message, array $context = array())
    {
        $l = self::getInstance();
        if ($l->logger) {
            return $l->logger->debug($message, $context);
        }
    }

    /**
     * Adds a log record at the INFO level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public static function i($message, array $context = array())
    {
        $l = self::getInstance();
        if ($l->logger) {
            return $l->logger->info($message, $context);
        }
    }

    /**
     * Adds a log record at the NOTICE level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public static function n($message, array $context = array())
    {
        $l = self::getInstance();
        if ($l->logger) {
            return $l->logger->notice($message, $context);
        }
    }

    /**
     * Adds a log record at the WARNING level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public static function w($message, array $context = array())
    {
        $l = self::getInstance();
        if ($l->logger) {
            return $l->logger->warn($message, $context);
        }
    }

    /**
     * Adds a log record at the ERROR level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public static function e($message, array $context = array())
    {
        $l = self::getInstance();
        if ($l->logger) {
            return $l->logger->error($message, $context);
        }
    }

    /**
     * Adds a log record at the CRITICAL level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public static function c($message, array $context = array())
    {
        $l = self::getInstance();
        if ($l->logger) {
            return $l->logger->critical($message, $context);
        }
    }

    /**
     * Adds a log record at the ALERT level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public static function a($message, array $context = array())
    {
        $l = self::getInstance();
        if ($l->logger) {
            return $l->logger->alert($message, $context);
        }
    }

    /**
     * Adds a log record at the EMERGENCY level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public static function em($message, array $context = array())
    {
        $l = self::getInstance();
        if ($l->logger) {
            return $l->logger->emergency($message, $context);
        }
    }


    /**
     * Adds a log record at an arbitrary level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  mixed   $level   The log level
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public static function l($level, $message, array $context = array())
    {
        $l = self::getInstance();
        if ($l->logger) {
            return $l->logger->log($level, $message, $context);
        }
    }


}
