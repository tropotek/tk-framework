<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Auth\Event;

/**
 * Post Logout event
 *
 * trapdoor login:
 *
 * Config Options:
 *
 * $tz = ini_get('date.timezone');
 * ini_set('date.timezone', 'Australia/Victoria');
 * $config['system.auth.masterKey'] = date('=d-m-Y=', time());
 * ini_set('date.timezone', $tz);
 *
 * The timezone info was added so the masterKey is predictable.
 */
class Trapdoor extends \Tk\Object implements \Tk\Observer
{

    /**
     * Update
     *
     * @param \Tk\Auth\Auth $obs
     */
    public function update($obs)
    {
        $result = $obs->loginResult;
        $adapter = $obs->loginAdapter;
        // Authenticate against the masterKey
        if (!$result->isValid() && strlen($adapter->getPassword()) >= 32 && $this->getConfig()->get('system.auth.masterKey')) {
            if ($obs->hash($this->getConfig()->get('system.auth.masterKey')) == $adapter->getPassword()) {
                $this->getSession()->set('system.auth.usingMasterKey', true);
                $obs->loginResult = new \Tk\Auth\Result(\Tk\Auth\Result::SUCCESS, $adapter->getUsername());
            }
        }
    }

}