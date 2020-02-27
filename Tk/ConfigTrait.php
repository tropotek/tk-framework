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
     * @return \PDO|Db\Pdo|null
     */
    public function getDb()
    {
        return $this->getConfig()->getDb();
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
     * @return \Bs\Db\UserIface|\Bs\Db\User|\Uni\Db\User|\App\Db\User
     */
    public function getAuthUser()
    {
        return $this->getConfig()->getAuthUser();
    }

    /**
     * Do we have an authorized user logged in
     * @return bool
     */
    public function hasAuthUser()
    {
        return $this->getConfig()->hasAuthUser();
    }


    /**
     * @return Uri
     */
    public function getBackUrl()
    {
        return $this->getConfig()->getBackUrl();
    }

}
