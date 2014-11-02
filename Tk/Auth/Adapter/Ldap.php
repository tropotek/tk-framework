<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Auth\Adapter;

/**
 * LDAP Authentication adapter
 *
 * This adapter checks the LDAP for a user with the correct name.
 * If found and validated then the system is checked for an existing user.
 * If a user exists on the LDAP system then the user is logged in normally.
 *
 *  $config['system.auth.loginAdapters'] = array( 'LDAP' => '\Tk\Auth\Adapter\Ldap' );
 *  $config['system.auth.ldap.enable'] = true;
 *  $config['system.auth.ldap.uri']    = 'ldap://ldap.example.au';
 *  $config['system.auth.ldap.port']   = 389;
 *  $config['system.auth.ldap.baseDn'] = 'ou=people,o=busname';
 *  $config['system.auth.ldap.userattr'] = 'uid';
 *
 *
 * @package Ext\Auth\Adapter
 */
class Ldap extends Iface
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
        if (!is_array($options) || !count($this->getConfig()->getGroup('system.auth.ldap'))) {
            $options = $this->getConfig()->getGroup('system.auth.ldap');
        }
        parent::__construct($username, $password, $options);
    }


    /**
     * Authenticate the user
     *
     * @throws \Tk\Auth\Adapter\Exception
     * @return \Tk\Auth\Result
     */
    public function authenticate()
    {
        if (!$this->getOption('system.auth.ldap.enable')) {
            return false;
        }
        if (!$this->getPassword()) {
            return $this->makeResult(\Tk\Auth\Result::FAILURE_CREDENTIAL_INVALID, 'Invalid account details');
        }

        $ldapUri = $this->getOption('system.auth.ldap.uri');
        $ldapPort = $this->getOption('system.auth.ldap.port');
        $ldapBaseDn = $this->getOption('system.auth.ldap.baseDn');
        $ldapFilter = sprintf('%s=%s', $this->getOption('system.auth.ldap.userattr'), $this->getUsername());

        $ldap = ldap_connect($ldapUri, $ldapPort);
        try {
            ldap_start_tls($ldap);
            $b = ldap_bind($ldap, $ldapFilter . ',' . $ldapBaseDn, $this->getPassword());
            if (!$b) throw new \Tk\Auth\Exception('1000: Failed to authenticate in LDAP');
        } catch (\Exception $e) {
            tklog($e->toString());
            return $this->makeResult(\Tk\Auth\Result::FAILURE_CREDENTIAL_INVALID, '1000: Failed to authenticate in LDAP');
        }
        if (!$ldap) {
            return $this->makeResult(\Tk\Auth\Result::FAILURE_CREDENTIAL_INVALID, '1001: Failed to authenticate in LDAP');
        }

        $user = \Usr\Db\User::getMapper()->findForAuth($this->getUsername());
        if ($user && $user->active) {
            return $this->makeResult(\Tk\Auth\Result::SUCCESS, 'User Found!');
        }

        return $this->makeResult(\Tk\Auth\Result::FAILURE_CREDENTIAL_INVALID, 'Invalid account details');
    }



}