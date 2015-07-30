<?php
/*
 * Created by PhpStorm.
 * User: godar
 * Date: 7/30/15
 * Time: 7:14 PM
 */

namespace Tk\Db;

/**
 * Class Model
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
abstract class Model
{
    /**
     * The Table Primary Key (Usually)
     * @var int
     */
    public $id = 0;





    /**
     * Get this object's DB mapper
     *
     * The Mapper class will be taken from this class's name if not supplied
     *
     * By default the Database is attempted to be seet from the Tk\Config object if it exists
     *
     * Also the Default table name is generated from this object: EG: /App/Db/WebUser = 'webUser'
     *
     * The 2 parameters must be set if you do not wish to use these defaults
     *
     * @param string $mapperClass
     * @return Mapper
     */
    static function getMapper($mapperClass = '')
    {
        if (!$mapperClass) {
            $class = get_called_class();
            $mapperClass = $class . 'Map';
        }
        $mapper = Mapper::create($mapperClass);
        if (!$mapper->getDb() && class_exists('\Tk\Config') && \Tk\Config::getInstance()->getDb()) {
            $mapper->setDb(\Tk\Config::getInstance()->getDb());
        }
        if (!$mapper->getTable()) {
            $a = explode('\\', $class);
            $table = lcfirst(array_pop($a));
            $mapper->setTable($table);
        }
        return $mapper;
    }



    /**
     * Insert the object into storage.
     * By default this is a database
     *
     * @return int The insert ID
     */
    public function insert()
    {
        $id = self::getMapper()->insert($this);
        $this->id = $id;
        return $this->getId();
    }

    /**
     * Update the object in storage
     *
     * @return int
     */
    public function update()
    {
        $r = self::getMapper()->update($this);
        return $r;
    }

    /**
     * A Utility method that checks the id and does and insert
     * or an update  based on the objects current state
     *
     */
    public function save()
    {
        if ($this->id) {
            $this->update();
        } else {
            $this->insert();
        }
    }

    /**
     * Delete the object from the DB
     *
     * @return int
     */
    public function delete()
    {
        $r = self::getMapper()->delete($this);
        return $r;
    }


    /**
     * Returns the object id if it is greater than 0 or the nextInsertId if is 0
     *
     * @return int
     */
    public function getVolatileId()
    {
        if (!$this->id) {
            return self::getMapper()->getDb()->getNextInsertId(self::getMapper()->getTable());
        }
        return $this->id;
    }







}