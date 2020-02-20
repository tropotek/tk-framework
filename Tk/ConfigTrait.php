<?php
namespace Tk;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2019 Michael Mifsud
 */
trait ConfigTrait
{

    /**
     * @return Config|\Bs\Config|\Uni\Config|\App\Config
     */
    public function getConfig()
    {
        return Config::getInstance();
    }

    /**
     * @param string $name
     * @return \PDO|Db\Pdo|null
     */
    public function getDb($name = 'db')
    {
        return $this->getConfig()->getDb($name);
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->getConfig()->getRequest();
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->getConfig()->getSession();
    }

    /**
     * Return the currently authenticated (logged in) user
     * @return \Bs\Db\UserIface|\Bs\Db\User|\App\Db\User
     */
    public function getAuthUser()
    {
        return $this->getConfig()->getAuthUser();
    }

    /**
     * @return Uri
     */
    public function getBackUrl()
    {
        return $this->getConfig()->getBackUrl();
    }

}
