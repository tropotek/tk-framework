<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Auth\Adapter;

/**
 * A authenticator adaptor
 *
 * Config Options:
 *
 * $tz = ini_get('date.timezone');
 * ini_set('date.timezone', 'Australia/Victoria');
 * $config['system.auth.masterKey'] = date('=d-m-Y=', time());
 * ini_set('date.timezone', $tz);
 *
 */
class Trapdoor extends Iface
{

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
        // Authenticate against the masterKey
        if (strlen($this->getPassword()) >= 32 && $this->getConfig()->get('system.auth.masterKey')) {
            if ($this->getConfig()->getAuth()->hash($this->getConfig()->get('system.auth.masterKey')) == $this->getPassword()) {
                $this->getSession()->set('system.auth.usingMasterKey', true);
                return $this->makeResult(\Tk\Auth\Result::SUCCESS);
            }
        }
        return $this->makeResult(\Tk\Auth\Result::FAILURE, array('username' => 'Invalid User details'));
    }


}
