<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Log\Adapter;

/**
 * A log observer interface...
 *
 * @package Tk\Log\Adapter
 */
abstract class Iface extends \Tk\Object implements \Tk\Observer
{
    /**
     * The level of errors to show using bit masking
     * Eg:
     * <code>
     *   ... ->setLevel(Tk_Log::NOTICE | Tk_Log::ERROR);
     * </code>
     * This will only show notice and error logs others will be ignored.
     * @var int
     */
    protected $level = 0;

    /**
     * constructor
     *
     * @param int $level
     */
    public function __construct($level)
    {
        $this->setLevel($level);
    }

    /**
     * Get code level.
     * The max code level to execute message
     *  o Tk\Log::DISABLED = 0 = disabled
     *  o Tk\Log::DEBUG = 0 = show all
     *
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set the max code level
     * Use Tk\Log static params as valid values.
     *
     * @param int $i
     * @return \Tk\Log\Iface
     */
    public function setLevel($i)
    {
        if (is_string($i)) {
            $i = eval('return ' . $i . ';');
        }
        $this->level = $i;
        return $this;
    }


    /**
     *Get Dump
     *
     * @return string
     */
    static function getDefaultDump($withTrace = true)
    {
        $request = \Tk\Request::getInstance();
        $str = "\n";
        $str .= "PHP:           `" . \PHP_VERSION . '` (' . \PHP_OS . ")\n";
        $str .= "URI:           `" . $request->getRequestUri()->toString() . "`\n";
        if ($request->getReferer()) {
            $str .= "Referrer:      `" . $request->getReferer()->toString() . "`\n";
        }
        if (isset($_SERVER['SERVER_NAME']) && isset($_SERVER['SERVER_ADDR'])) {
            $str .= "Server:        `" . ($_SERVER['SERVER_NAME'] != '' ? $_SERVER['SERVER_NAME'] : $_SERVER['SERVER_ADDR']) . "`\n";
        }

        $str .= "Client:        `" . $request->getRemoteAddr() . "`\n";
        $str .= "User Agent:    `" . $request->getUserAgent() . "`\n";
        $str .= "\n";

        $str .= \Tk\Debug\Vd::getTrace();

        return $str;
    }

}