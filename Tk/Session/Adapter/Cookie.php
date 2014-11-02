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

    protected $encrypt = false;


    public function __construct()
    {

        $this->cookieName = \Tk\Config::getInstance()->get('session.name') . '_data';
        $this->encrypt = \Tk\Config::getInstance()->get('session.encryption');
        \Tk\Log\Log::write('Session Cookie Driver Initialized');
    }

    public function open($path, $name)
    {
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($id)
    {
        $data = (string)\Tk\Request::getInstance()->getCookie($this->cookieName);
        if ($data == '') {
            return $data;
        }
        return empty($this->encrypt) ? base64_decode($data) : \Tk\Encrypt::decode($data);
    }

    public function write($id, $data)
    {
        $data = empty($this->encrypt) ? base64_encode($data) : \Tk\Encrypt::encode($data);

        if (strlen($data) > 4048) {
            \Tk\Log\Log::write('\Tk\Session (' . $id . ') data exceeds the 4KB limit, ignoring write.', \Tk\Log\Log::ERROR);
            return false;
        }
        return \Tk\Request::getInstance()->setCookie($this->cookieName, $data, time() + \Tk\Config::getInstance()->get('session.expiration'));
    }

    public function destroy($id)
    {
        return \Tk\request::getInstance()->deleteCookie($this->cookieName);
    }

    public function regenerate()
    {
        session_regenerate_id(true);
        // Return new id
        return session_id();
    }

    public function gc($maxlifetime)
    {
        return true;
    }

}