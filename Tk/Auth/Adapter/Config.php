<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Auth\Adapter;

/**
 * A Config admin authenticator adaptor
 *
 * To enable this adapter, add the following to your config:
 *
 * $config['system.auth.loginAdapters'] = array('Config' => '\Tk\Auth\Adapter\Config');
 * $config['system.auth.username'] = 'admin';
 * $config['system.auth.password'] = 'password';
 *
 * Usefull for single user sites, such as admin areas.
 *
 */
class Config extends Iface
{

    /**
     * Constructor
     *
     * @param  string $username The username of the account being authenticated
     * @param  string $password The password of the account being authenticated
     * @param  array  $options  An array of config options, if null then $config[`system.auth.ldap`] is used
     */
    public function __construct($username = null, $password = null, $options = null)
    {
        if (!is_array($options)) {
            $options['system.auth.userKey'] = 'system.auth.username';
            $options['system.auth.passKey'] = 'system.auth.password';
        }
        parent::__construct($username, $password, $options);
    }

    /**
     * authenticate() - defined by Tk_Auth_Adapter_Interface.  This method is called to
     * attempt an authentication.  Previous to this call, this adapter would have already
     * been configured with all necessary information to successfully connect to a database
     * table and attempt to find a record matching the provided identity.
     *
     * @return \Tk\Auth\Result
     */
    public function authenticate()
    {
        $cUserKey = $this->getConfig()->get($this->getOption('system.auth.userKey'));
        $cPassKey = $this->getConfig()->get($this->getOption('system.auth.passKey'));

        if ($cUserKey && $cPassKey) {
            if ($this->getUsername() === $cUserKey && $this->getPassword() === $cPassKey) {
                return $this->makeResult( \Tk\Auth\Result::SUCCESS);
            }
        }

        return $this->makeResult(\Tk\Auth\Result::FAILURE, array('username' => 'Invalid User details'));
    }

}
