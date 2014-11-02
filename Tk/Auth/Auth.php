<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Auth;

/**
 * This Auth object validates a user and manages a user session/cookie/object
 *
 * It is better to use \Tk\Config::getInstance()->getAuth()
 * This ensures a single instance and allows for observers to be attached
 * ans executed as required.
 *
 * Common observer events:
 *  o preLogin
 *  o postLogin
 *  o preLogout
 *  o postLogout
 *  o postCreateUser
 *  o postActivateUser
 *  o postRecoverUser
 *
 * @package Tk\Auth
 */
class Auth extends \Tk\Object
{


    const P_ADMIN = 'admin';
    const P_USER = 'user';
    const P_PUBLIC = 'public';

    /**
     * Persistent storage handler
     *
     * @var \Tk\Auth\Storage\Iface
     */
    protected $storage = null;

    /**
     * @var array
     */
    protected $params = array();

    /**
     * @var array
     */
    protected $hashFunc = '';

    /**
     * @var \Tk\Auth\Result
     */
    public $loginResult = null;

    /**
     * @var \Tk\Auth\Adapter\Iface
     */
    public $loginAdapter = null;


    /**
     *
     * @param string $hashFunc Default 'MD5' password hashing
     */
    public function __construct($hashFunc = 'md5')
    {
        $this->hashFunc = $hashFunc;
    }

    /**
     * Create a random password
     *
     * @param int $length
     * @return string
     */
    public function createPassword($length = 8)
    {
        $chars = '234567890abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ';
        $i = 0;
        $password = '';
        while ($i <= $length) {
            $password .= $chars[mt_rand(0, strlen($chars) - 1)];
            $i++;
        }
        return $password;
    }

    /**
     * Returns true if and only if an identity is available from storage
     *
     * @return bool
     */
    public function hasIdentity()
    {
        return !$this->getStorage()->isEmpty();
    }

    /**
     * Returns the user details from storage or null if non is available
     *
     * @return mixed
     */
    public function getIdentity()
    {
        $storage = $this->getStorage();
        if ($storage->isEmpty()) {
            return null;
        }
        return $storage->read();
    }


    /**
     * Create a hash using the config defined function
     * NOTE:
     *   If the has function is changed after the site
     *   is installed major problems can occur to fix
     *   you will have to reset all user passwords.
     *
     * @param string $str
     * @return string (Hashed string to store or compare)
     */
    public function hash($str)
    {
        $func = $this->hashFunc;
        $hash = $func($str);
        return $hash;
    }


    /**
     * Set a parameter for retreval later
     *
     * @param string $name
     * @param mixed $value
     * @return \Tk\Auth\Auth
     */
    public function set($name, $value)
    {
        $this->params[$name] = $value;
        return $this;
    }

    /**
     * Get a parameter
     *
     * @param string $name
     * @return mixed
     */
    public function get($name)
    {
        if ($this->exists($name)) {
            return $this->params[$name];
        }
    }

    /**
     * Does a parameter exist in the params array
     *
     * @param string $name
     * @return booolean
     */
    public function exists($name)
    {
        return isset($this->params[$name]);
    }

    /**
     * Returns the persistent storage handler
     * \Tk\Session storage is used by default unless a different storage adapter has been set.
     *
     * @return \Tk\Auth\Storage\Iface
     */
    public function getStorage()
    {
        if ($this->storage === null) {
            $this->setStorage(new Storage\Session());
        }
        return $this->storage;
    }

    /**
     * Sets the persistent storage handler
     *
     * @param  \Tk\Auth\Storage\Iface $storage
     * @return \Tk\Auth\Auth
     */
    public function setStorage(Storage\Iface $storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * Authenticates against the supplied adapter
     *
     * @param  \Tk\Auth\Adapter\Iface $adapter
     * @return \Tk\Auth\Result
     */
    public function authenticate(Adapter\Iface $adapter)
    {
        // Clear storage
        if ($this->hasIdentity()) {
            $this->clearIdentity();
        }
        $this->loginAdapter = $adapter;
        $this->notify('preLogin');
        $this->loginResult = $adapter->authenticate();
        $this->notify('postLogin');
        if ($this->loginResult->isValid()) {
            $this->getStorage()->write($this->loginResult->getIdentity());
        }
        return $this->loginResult;
    }


    /**
     * Clears the user details from persistent storage
     *
     * @return \Tk\Auth\Auth
     */
    public function clearIdentity()
    {
        $this->notify('preLogout');
        $this->getStorage()->clear();
        $this->notify('postLogout');
        return $this;
    }

}
