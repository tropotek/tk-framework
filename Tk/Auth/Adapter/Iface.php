<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Auth\Adapter;

/**
 * Adapter Interface
 * 
 *
 * @package Tk\Auth\Adapter
 */
abstract class Iface extends \Tk\Object
{

    /**
     * The array of adapter options
     *
     * @var array
     */
    protected $options = null;

    /**
     * The username of the account being authenticated.
     * @var string
     */
    protected $username = null;

    /**
     * The password of the account being authenticated.
     * @var string
     */
    protected $password = null;



    /**
     * Constructor
     *
     * @param  string $username The username of the account being authenticated
     * @param  string $password The password of the account being authenticated
     * @param  array  $options  An array of config options, if null then $config[`system.auth.ldap`] is used
     */
    public function __construct($username = null, $password = null, $options = null)
    {
        $this->options = $options;
        if ($username !== null) {
            $this->setUsername($username);
        }
        if ($password !== null) {
            $this->setPassword($password);
        }
    }


    /**
     * Performs an authentication attempt
     *
     * @return \Tk\Auth\Result
     * @throws \Tk\Auth\Exception If authentication cannot be performed
     */
    abstract public function authenticate();



    /**
     *
     * @param $options
     * @return Iface
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Return an option value
     *
     * @param string $key
     * @return array
     */
    public function getOption($key)
    {
        if (isset($this->options[$key])) {
            return $this->options[$key];
        }
    }

    /**
     * Returns the username of the account being authenticated, or
     * NULL if none is set.
     *
     * @return string|null
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Sets the username for binding
     *
     * @param  string $username The username for binding
     * @return Iface
     */
    public function setUsername($username)
    {
        $this->username = (string) $username;
        return $this;
    }

    /**
     * Returns the password of the account being authenticated, or
     * NULL if none is set.
     *
     * @return string|null
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Sets the password for the account
     *
     * @param  string $password The password of the account being authenticated
     * @return Iface
     */
    public function setPassword($password)
    {
        $this->password = (string) $password;
        return $this;
    }


    /**
     * Create a login result object
     *
     * @param $code
     * @param string|array $messages (optional)
     * @param string $identity (optional) If null then we use $this->getUsername()
     * @return \Tk\Auth\Result
     */
    protected function makeResult($code, $messages = array(), $identity = null)
    {
        if (!is_array($messages)) {
            $messages = array('username' => $messages);
        }
        if (!$identity) {
            $identity = $this->getUsername();
        }
        return new \Tk\Auth\Result($code, $identity, $messages);
    }



}