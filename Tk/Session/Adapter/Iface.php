<?php
/*
 * @author Michael Mifsud <http://www.tropotek.com/>
 * @see http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Session\Adapter;

interface Iface extends \SessionHandlerInterface
{

    /**
     * @return  string
     */
    #[\ReturnTypeWillChange]
    public function regenerate();

    // SessionHandlerInterface

    /**
     * @return  boolean
     */
    #[\ReturnTypeWillChange]
    public function close();

    /**
     * @param   string $sessionId
     * @return  boolean
     */
    #[\ReturnTypeWillChange]
    public function destroy($sessionId);

    /**
     * Garbage collection.
     *
     * @param   integer $maxlifetime session expiration period
     * @return  int|false
     */
    #[\ReturnTypeWillChange]
    public function gc($maxlifetime);
    
    /**
     * @param string $path The path where to store/retrieve the session.
     * @param string $sessionId The session id.
     * @return  boolean
     */
    #[\ReturnTypeWillChange]
    public function open($path, $sessionId);

    /**
     * @param   string $sessionId
     * @return  string|false
     */
    #[\ReturnTypeWillChange]
    public function read($sessionId);

    /**
     * @param   string $sessionId
     * @param   string $data
     * @return  boolean
     */
    #[\ReturnTypeWillChange]
    public function write($sessionId, $data);

}

