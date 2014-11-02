<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Db;

/**
 * A base Data Model Object.
 *
 * NOTICE: All model objects should not use `private` for db field properties.
 * This will cause fields not to load from the DB, only public and protected
 * properties are supported with this data mapper.
 *
 *
 * @package Tk\Db
 */
class Model extends \Tk\Model\Model
{

    // NOTICE: The following are pure helper methods
    //  and should not be considered/used as part of the
    //  database mapper system


    /**
     * Insert the object into storage.
     * By default this is a database
     *
     * @return int The object insert ID
     */
    public function insert()
    {
        $this->notify('preInsert');
        $this->id = $this->getMapper()->insert($this);
        $this->notify('postInsert');
        return $this->id;
    }

    /**
     * Update the object in storage
     * By default this is a database
     *
     * @return int
     */
    public function update()
    {
        $this->notify('preUpdate');
        $r = $this->getMapper()->update($this);
        $this->notify('postUpdate');
        return $r;
    }

    /**
     * A Utility method that checks the id and does and insert
     * or an update  based on the objects current state
     *
     * @return int
     */
    public function save()
    {
        $this->notify('preSave');
        if ($this->id) {
            $r = $this->update();
        } else {
            $r = $this->insert();
        }
        $this->notify('postSave');
        return $r;
    }

    /**
     * Test if the object is deleted
     *
     * @return bool
     */
    public function isDeleted()
    {
        return $this->getMapper()->isDeleted($this);
    }

    /**
     * Delete the object from the DB
     * This method also extends on that and looks for any data folder
     * and deletes that too. By default the data folder is assumed to be
     * if the format of: {siteDataPath}/{className}
     *
     * Eg:
     *   Class = \Ext\Db\MenuItem --> Path = {siteDataPath}/MenuItem
     *
     * @return int
     */
    public function delete()
    {
        $this->notify('preDelete');

        // Delete any data for this object
        $arr = explode('_', $this->getClassName());
        $path = $this->getConfig()->getDataPath() . '/' . array_pop($arr);
        if (is_dir($path)) {
            \Tk\Path::rmdir(dirname($this->getConfig()->getDataPath() . $this->icon));
        }

        $r = $this->getMapper()->delete($this);

        $this->notify('postDelete');
        return $r;
    }

    /**
     * Returns the object id if it is greater than 0 or the nextInsertId if is 0
     *
     * @return int
     */
    public function getVolitileId()
    {
        return $this->getMapper()->getVolitileId($this);
    }

    /**
     * Get the object's DB mapper
     *
     * @return \Tk\Db\Mapper
     */
    static function getMapper()
    {
        return Mapper::get(get_called_class());
    }

    /**
     * Get the object's DB mapper
     *
     * @param $id
     * @return self
     */
    static function find($id)
    {
        return self::getMapper()->find($id);
    }

    /**
     * Get the object's DB mapper
     *
     * @param \Tk\Db\Tool $tool
     * @return \Tk\Db\Array
     */
    static function findAll($tool = null)
    {
        return self::getMapper()->findAll($tool);
    }

}
