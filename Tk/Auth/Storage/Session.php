<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Auth\Storage;

/**
 *
 *
 *
 * @package Tk\Auth\Storage
 */
class Session extends \Tk\Object implements Iface
{
    /**
     * Default session namespace
     */
    const SID_DEFAULT = '__Tk_Auth';

    /**
     * Session namespace
     *
     * @var mixed
     */
    protected $sid = '';



    /**
     * Sets session storage options and initializes session namespace object
     *
     * @param  string $sid
     */
    public function __construct($sid = self::SID_DEFAULT)
    {
        $this->sid = $sid;
    }

    /**
     * Returns the session namespace for this storage object
     *
     * @return string
     */
    public function getSid()
    {
        return $this->sid;
    }

    /**
     * Defined by \Tk\Auth\Storage\Iface
     *
     * @return bool
     */
    public function isEmpty()
    {
        return !$this->getSession()->exists($this->getSid());
    }

    /**
     * Defined by \Tk\Auth\Storage\Iface
     *
     * @return mixed
     */
    public function read()
    {
        return $this->getSession()->get($this->getSid());
    }

    /**
     * Defined by \Tk\Auth\Storage\Iface
     *
     * @param  mixed $contents
     * @return void
     */
    public function write($contents)
    {
        $this->getSession()->set($this->getSid(), $contents);
    }

    /**
     * Defined by \Tk\Auth\Storage\Iface
     *
     * @return void
     */
    public function clear()
    {
        $this->getSession()->delete($this->getSid());
    }
}
