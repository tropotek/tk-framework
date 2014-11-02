<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Controller;

/**
 *
 * @package Tk\Controller
 */
class Maintenance extends \Tk\Object implements Iface
{

    /**
     *
     * @param \Tk\FrontController $obj
     */
    public function update($obj)
    {
        tklog($this->getClassName() . '::update()');

        // Check for Maintenance Mode
        if (!$this->getConfig()->get('system.maintenance.enable')) {
            return;
        }

        // check permissions if a real user object
        if ($this->getConfig()->getUser() && $this->getConfig()->getUser() instanceof \Usr\Db\User) {
            if ($this->getConfig()->getUser()->hasPermission($this->getConfig()->get('system.maintenance.access.permission'))) {
                return;
            }
        }

        // Check IP's
        $whitelist = $this->getConfig()->get('system.maintenance.access.ip');
        if ($whitelist) {
            if (!is_array($whitelist)) $whitelist = explode (',', $whitelist);
            foreach ($whitelist as $ip) {
                $ip = trim($ip);
                if (inet_pton($ip) !== false) {
                    if ($ip == $this->getRequest()->getRemoteAddr()) return;
                }
            }
        }
        $this->getConfig()->getResponse()->sendError($this->getConfig()->get('system.maintenance.message'), \Tk\Response::SC_SERVICE_UNAVAILABLE);
        exit;

    }

}