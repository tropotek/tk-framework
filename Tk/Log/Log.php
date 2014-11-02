<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Log;

/**
 * This is the lib main object
 *
 * If set in the Config setting '_disableLog' as a get parameter can dissable the log for a url.
 *
 *
 *
 * @package Tk\Log
 */
class Log extends \Tk\Object
{
    //static $logDateFormat = 'Y-m-d H:i:s';
    static $logDateFormat = 'H:i:s';

    /**
     * @var Log
     */
    static $instance = null;

    // Log codes
    const DISABLED = 0;
    const DEBUG = 1;
    const NOTICE = 2;
    const ERROR = 4;
    const EMAIL = 8;
    const SYSTEM = 16;
    const MESSAGE = 32;

    /**
     * @var array
     */
    static $labels = array(
        self::DISABLED => 'DISABLED',
        self::DEBUG => 'DEBUG',
        self::NOTICE => 'NOTICE',
        self::ERROR => 'ERROR',
        self::EMAIL => 'EMAIL',
        self::SYSTEM => 'SYSTEM',
        self::MESSAGE => 'MESSAGE'
    );

    /**
     * @var string
     */
    private $header = '';

    /**
     * @var string
     */
    private $message = '';

    /**
     * @var int
     */
    private $type = 0;

    /**
     * @var bool
     */
    private $enabled = true;

    private $args = null;



    /**
     * Sigleton, No instances can be created.
     */
    private function __construct() { }

    /**
     * Get an instance of this object
     *
     * @return Log
     */
    static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * A factory method to log data to the logfile.
     *
     * @param string $message
     * @param int $type
     * @param array $args
     * @return Log
     */
    static function write($message, $type = self::NOTICE, $args = array())
    {
        return self::getInstance()->writeLine($message, $type, $args);
    }

    /**
     * Get the log message header
     *
     * @return string
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * Get the log message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Get the log code
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the status of the log.
     *
     * @param bool $b
     * @return Log
     */
    public function setEnabled($b = true)
    {
        $this->enabled = $b;
        return $this;
    }

    /**
     * Any arguments sent to the log write method.
     *
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * Reset the message buffer
     */
    protected function reset()
    {
        $this->header = '';
        $this->message = '';
        $this->type = 0;
    }

    /**
     * Write a log message to the system
     *
     * @param string $message
     * @param int $type (optional) Default Log::NOTICE
     * @param array $args (optional) Any extra args for log plugins in an array
     * @return Log
     */
    public function writeLine($message, $type = self::NOTICE, $args = array())
    {
        if (!$this->enabled) return;
        $this->header = sprintf('[%s][%5.2f][%9s][%s]', date(self::$logDateFormat), round(\Tk\FrontController::scriptDuration(), 2),
                \Tk\Path::bytes2String(memory_get_usage(false)), self::$labels[$type]);
        $this->message = $message;
        $this->type = $type;
        $this->args = $args;
        $this->notify();
        $this->reset();
        return $this;
    }

}