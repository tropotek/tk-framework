<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Session\Adapter;

/**
 * A session object
 *
 */
interface Iface extends \SessionHandlerInterface
{
    

    
    
    /**
     * Regenerates the session id.
     *
     * @return  string
     */
    public function regenerate();
    
    
    
    
    // SessionHandlerInterface
    

    /**
     * Closes a session.
     *
     * @return  boolean
     */
    public function close();

    /**
     * Destroys a session.
     *
     * @param   string $sessionId
     * @return  boolean
     */
    public function destroy($sessionId);

    /**
     * Garbage collection.
     *
     * @param   integer $maxlifetime session expiration period
     * @return  boolean
     */
    public function gc($maxlifetime);
    
    /**
     * Opens a session.
     *
     * @param string $path The path where to store/retrieve the session.
     * @param string $sessionId The session id.
     * @return  boolean
     */
    public function open($path, $sessionId);

    /**
     * Reads a session.
     *
     * @param   string $sessionId
     * @return  string
     */
    public function read($sessionId);

    /**
     * Writes a session.
     *
     * @param   string $sessionId
     * @param   string $data
     * @return  boolean
     */
    public function write($sessionId, $data);

}

