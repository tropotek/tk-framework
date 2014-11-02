<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Session\Adapter;

/**
 * A session object
 *
 * @package Tk\Session\Adapter
 */
interface Iface
{

    /**
     * Opens a session.
     *
     * @param   string $path  save path
     * @param   string $name  session name
     * @return  boolean
     */
    public function open($path, $name);

    /**
     * Closes a session.
     *
     * @return  boolean
     */
    public function close();

    /**
     * Reads a session.
     *
     * @param   string $id session id
     * @return  string
     */
    public function read($id);

    /**
     * Writes a session.
     *
     * @param   string $id  session id
     * @param   string $data  session data
     * @return  boolean
     */
    public function write($id, $data);

    /**
     * Destroys a session.
     *
     * @param   string $id  session id
     * @return  boolean
     */
    public function destroy($id);

    /**
     * Regenerates the session id.
     *
     * @return  string
     */
    public function regenerate();

    /**
     * Garbage collection.
     *
     * @param   integer $maxlifetime session expiration period
     * @return  boolean
     */
    public function gc($maxlifetime);

}

