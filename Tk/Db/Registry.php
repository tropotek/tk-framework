<?php

/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

namespace Tk\Db;

/**
 * A DB registry object
 *
 * The SQL for the registry if you plan to save data to the DB
 * <code>
 * -- --------------------------------------------------------
 * --
 * -- Table structure for table `config`
 * --
 * DROP TABLE IF EXISTS `{$table}`;
 * CREATE TABLE IF NOT EXISTS `{$table}` (
 *   `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
 *   `group` VARCHAR(64) NOT NULL DEFAULT 'system',
 *   `key` VARCHAR(64) NOT NULL DEFAULT '',
 *   `value` TEXT,
 *   PRIMARY KEY (`id`),
 *   KEY `key` (`key`),
 *   KEY `group` (`group`),
 *   UNIQUE `group_2` (`group`, `key`)
 * ) ENGINE=InnoDB;
 * </code>
 *
 *
 *
 *
 * @TODO IMPORTANT Finish this object!!!!!!!!!!!!!!!!!
 * @info I want it to be an abstract for all data tables IE userData, pluginData, etc....
 *
 * @TODO We need to seperate the registry into a DB Registry object to remove the DB access
 *     methods. This is a messy object as it stands and is confusing for DB data and memory data.
 *     The new DB registry object should be able to exchange data into other Registry instances.
 *
 *
 *
 * @package Tk\Db
 */
class Registry extends \Tk\Registry
{

    /**
     * @var string
     */
    protected $dbTable = 'config';

    /**
     * @var string
     */
    protected $dbGroup = 'system';

    /**
     * @var \Tk\Db\Pdo
     */
    protected $db = null;


    /**
     * create
     *
     * @param string $dbTable
     * @param string $dbGroup
     * @return \Tk\Db\Registry
     */
    static function createDbRegistry($dbTable, $dbGroup)
    {
        $obj = new self();
        $obj->dbTable = $dbTable;
        $obj->dbGroup = $dbGroup;
        $obj->db = \Tk\Config::getInstance()->getDb();
        $obj->loadFromDb();
        return $obj;
    }


    /**
     * Get the table name that the registry is stored in.
     *
     * @return string
     */
    public function getDbTable()
    {
        return $this->dbTable;
    }

    /**
     * load the registry with the values from the DB
     *
     */
    public function loadFromDb()
    {
        $sql = sprintf('SELECT * FROM %s WHERE `group` = %s ', $this->getDbTable(), $this->db->quote($this->dbGroup));
        $res = $this->db->query($sql);

        foreach ($res as $row) {
            $this->set($row->key, $row->value);
        }
    }

    /**
     * Save all the values from the registry to the DB
     *
     */
    public function saveToDb()
    {
        foreach ($this->getArray() as $k => $v) {
            $this->dbSet($k, $v);
        }
    }



    /**
     *
     * @param string $offset
     */
    public function offsetSet($offset, $val)
    {
        $this->dbSet($offset, $val);
        parent::offsetSet($offset, $val);
    }

    /**
     *
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        $this->dbUnset($offset);
        parent::offsetUnset($offset);
    }


    /**
     * set a value in the DB
     *
     * @param type $key
     * @param type $val
     * @return type
     */
    public function dbSet($key, $val)
    {
        if (is_array($val) || is_object($val)) {
            //$val = serialize($val);
            return false;
        }
        if ($this->dbExists($key)) {
            $sql = sprintf('UPDATE %s SET value = %s WHERE `key` = %s AND `group` = %s ', $this->getDbTable(), $this->db->quote($val),
                $this->db->quote($key), $this->db->quote($this->dbGroup));
            return $this->db->query($sql);
        } else {
            $sql = sprintf('INSERT INTO %s VALUES (NULL , %s, %s, %s)', $this->getDbTable(), $this->db->quote($this->dbGroup),
                $this->db->quote($key), $this->db->quote($val));
            return $this->db->query($sql);
        }
    }

    /**
     * get a value from the table
     *
     * @param type $key
     * @return string
     */
    public function dbGet($key)
    {
        $sql = sprintf('SELECT * FROM %s WHERE `key` = %s AND `group` = %s ', $this->getDbTable(), $this->db->quote($key), $this->db->quote($this->dbGroup));
        $res = $this->db->query($sql);
        $row = $res->fetchObject();
        if ($row) {
            return $row->value;
        }
        return '';
    }

    /**
     * Get all group values from the table
     *
     * @return array
     */
    public function dbGetByGroup()
    {
        $sql = sprintf('SELECT * FROM %s WHERE `group` = %s ', $this->getDbTable(), $this->db->quote($this->dbGroup));
        $res = $this->db->query($sql);
        $result = array();
        foreach($res->fetchAll() as $row) {
            $result[$row->key] = $row->value;
        }
        return $result;
    }

    /**
     * Check if a key exists
     *
     * @param string $key
     * @return bool
     */
    public function dbExists($key)
    {
        $sql = sprintf('SELECT * FROM %s WHERE `key` = %s AND `group` = %s ', $this->getDbTable(), $this->db->quote($key), $this->db->quote($this->dbGroup));
        $res = $this->db->query($sql);
        if ($res->rowCount() == 1) {
            return true;
        }
        return false;
    }

    /**
     * Delete a key from the table
     *
     * @param string $key
     * @return \PdoResult
     */
    public function dbUnset($key)
    {
        $sql = sprintf('DELETE FROM %s WHERE `key` = %s AND `group` = %s ', $this->getDbTable(), $this->db->quote($key), $this->db->quote($this->dbGroup));
        return $this->db->query($sql);
    }

    /**
     * alias for dbUnset
     *
     * @param string $key
     * @return \PdoResult
     */
    public function dbDelete($key)
    {
        return $this->dbUnset($key);
    }


}

