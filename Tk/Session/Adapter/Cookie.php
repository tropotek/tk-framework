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
     * @param string $name
     * @return bool
     */
    public function open($path, $name)
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
     * @param string $id
     * @return string
     */
    public function read($id)
    {
        $data = (string)$this->cookie->get($this->cookieName);
        if ($data == '') {
            return $data;
        }
        return empty($this->encrypt) ? base64_decode($data) : \Tk\Encrypt::decode($data);
    }

    /**
     * @param string $id
     * @param string $data
     * @return bool
     */
    public function write($id, $data)
    {
        $data = empty($this->encrypt) ? base64_encode($data) : \Tk\Encrypt::encode($data);
        if (strlen($data) > 4048) {
            return false;
        }
        return $this->cookie->set($this->cookieName, $data, $this->expiration);
    }

    /**
     * @param string $id
     * @return bool
     */
    public function destroy($id)
    {
        return $this->cookie->delete($this->cookieName);
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