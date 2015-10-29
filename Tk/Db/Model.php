<?php
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
     * @var string
     */
    static public $MAPPER_APPEND = 'Map';


    /**
     * Take the raw data from a DB and populate this
     * Model object's instance
     *
     * @param array $row
     * @return Model
     */
    public function __dbmap(array $row) { }

    /**
     * Return an array formatted data ready for an sql query
     *
     * @return array|null
     */
    public function __dbunmap() { return; }


    /**
     * Get this object's DB mapper
     *
     * The Mapper class will be taken from this class's name if not supplied
     *
     * By default the Database is attempted to be set from the Tk\Config object if it exists
     *
     * Also the Default table name is generated from this object: EG: /App/Db/WebUser = 'webUser'
     *
     * This would in-turn look for the mapper class /App/Db/WebUserMap
     *
     * Change the self::$APPEND parameter to change the class append name
     *
     * The method setDb() must be called after calling getMapper() if you do not wish to use the DB from the config
     *
     *
     * @param string $mapperClass
     * @return Mapper
     *
     * @todo: not happy with this method as it stands.... fix it!
     */
    static function getMapper($mapperClass = '')
    {
        $append = self::$MAPPER_APPEND;
        $class = get_called_class();
        if (!$mapperClass) {
            $mapperClass = $class . $append;
        }
        $mapper = Mapper::create($mapperClass, $class);
        if (!$mapper->getDb() && class_exists('\Tk\Config') && \Tk\Config::getInstance()->getDb()) {
            $mapper->setDb(\Tk\Config::getInstance()->getDb());
        }
        if (!$mapper->getTable()) {
            $a = explode('\\', $mapperClass);
            $table = lcfirst(array_pop($a));
            $table = substr($table, 0, strrpos($table, $append));
            $mapper->setTable($table);
        }
        return $mapper;
    }

    /**
     * Get the model primary DB key, usually ID
     *
     * @return mixed
     */
    public function getId()
    {
        $pk = self::getMapper()->getPrimaryKey();
        if (property_exists($this, $pk)) {
            return $this->$pk;
        }
        return null;
    }

    /**
     * @param mixed $id
     * @return $this
     */
    public function setId($id)
    {
        $pk = self::getMapper()->getPrimaryKey();
        if (property_exists($this, $pk)) {
            $this->$pk = $id;
        }
        return $this;
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
        $this->setId($id);
        return $id;
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
        if ($this->getId()) {
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
        if (!$this->getId()) {
            return self::getMapper()->getDb()->getNextInsertId(self::getMapper()->getTable());
        }
        return $this->getId();
    }

}