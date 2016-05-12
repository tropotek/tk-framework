<?php
namespace Tk\Session\Adapter;


/**
 * Class Cookie
 * 
 * 
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 * 
 * @todo This object needs to be unit tested it is still not working correctly
 */
class Cookie implements Iface
{
    
    /**
     * @var string
     */
    protected $cookieName = '';

    /**
     * @var bool
     */
    protected $encrypt = false;

    /**
     * @var \Tk\Cookie
     */
    protected $cookie = null;

    /**
     * @var int
     */
    protected $expiration = 86400;


    /**
     * Cookie constructor.
     *
     * @param \Tk\Cookie $cookie
     * @param string $cookieName
     * @param int $expiration
     * @param bool $encrypt
     */
    public function __construct($cookie, $cookieName='session_data', $expiration = 86400, $encrypt = false)
    {
        $this->cookie = $cookie;
        $this->cookieName = $cookieName;
        $this->encrypt = $encrypt;
        $this->expiration = $expiration;
    }

    /**
     * @return string
     */
    public function regenerate()
    {
        session_regenerate_id(true);
        return session_id();
    }
    
    
    
    /**
     * @param string $path
     * @param string $sessionId
     * @return bool
     */
    public function open($path, $sessionId)
    {
        return true;
    }

    /**
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * read
     * 
     * @param string $sessionId
     * @return string
     */
    public function read($sessionId)
    {
        $data = (string)$this->cookie->get($this->cookieName);
        if ($data) {
            $data = $this->encrypt ? base64_decode($data) : \Tk\Encrypt::decode($data);
        }
        return $data;
    }

    /**
     * @param string $sessionId
     * @param string $data
     * @return bool
     */
    public function write($sessionId, $data)
    {
        $data = empty($this->encrypt) ? base64_encode($data) : \Tk\Encrypt::encode($data);
        if (strlen($data) > 4048) {
            return false;
        }
        $this->cookie->set($this->cookieName, $data, $this->expiration);
        //$this->cookie->set($this->cookieName, $data);
        return true;
    }

    /**
     * @param string $sessionId
     * @return bool
     */
    public function destroy($sessionId)
    {
        $this->cookie->delete($this->cookieName);
        return true;
    }

    /**
     * @param int $maxlifetime
     * @return bool
     */
    public function gc($maxlifetime)
    {
        return true;
    }

}