<?php
/*       -- TkLib Auto Class Builder --
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Tropotek Development
 *
 * Date: 6/23/14
 * Time: 8:38 AM
 */
namespace Tk\Traits;


/**
 * Access the data table in the DB for an object
 *
 *
 *
 */
trait ObjectData
{


    /**
     * hasDataKey
     *
     * @param string $key
     * @return boolean
     */
    public function hasDataKey($key)
    {
        $db = $this->getConfig()->getDb();
        $objectClass = $this->getClassName();
        $objectId = $this->getId();

        $sql = sprintf('SELECT * FROM `objectData` WHERE `objectId` = %s AND `objectClass` = %s AND `key` = %s ', (int)$objectId, $db->quote($objectClass), $db->quote($key));
        $res = $db->query($sql);
        $row = $res->fetch(\PDO::FETCH_OBJ);
        if ($row) {
            return true;
        }
        return false;
    }

    /**
     * getData
     *
     * @param string $key
     * @return mixed
     */
    public function getData($key)
    {
        $db = $this->getConfig()->getDb();
        $objectClass = $this->getClassName();
        $objectId = $this->getId();

        $sql = sprintf('SELECT * FROM `objectData` WHERE `objectId` = %s AND `objectClass` = %s AND `key` = %s ', (int)$objectId, $db->quote($objectClass), $db->quote($key));
        $res = $db->query($sql);
        $row = $res->fetch(\PDO::FETCH_OBJ);
        if ($row) {
            return stripslashes($row->value);
        }
    }

    /**
     * setData
     *
     * @param $key
     * @param $value
     * @return mixed
     */
    public function setData($key, $value)
    {
        $db = $this->getConfig()->getDb();
        $objectClass = $this->getClassName();
        $objectId = $this->getId();

        if ($this->hasDataKey($key)) {
            $sql = sprintf('UPDATE `objectData` SET value = %s WHERE `objectId` = %s AND `objectClass` = %s AND `key` = %s ', $db->quote($value), (int)$objectId, $db->quote($objectClass), $db->quote($key));
            $db->query($sql);
        } else {
            $sql = sprintf('INSERT INTO `objectData` (`objectId`, `objectClass`, `key`, `value`) VALUES (%s, %s, %s, %s)', (int)$objectId, $db->quote($objectClass), $db->quote($key), $db->quote($value) );
            $db->query($sql);
        }
        return $value;
    }

    /**
     * deleteData
     *
     * @param mixed $key
     * @return $this
     */
    public function deleteData($key)
    {
        $db = $this->getConfig()->getDb();
        $objectClass = $this->getClassName();
        $objectId = $this->getId();

        $sql = sprintf('DELETE FROM `objectData` WHERE  `objectId` = %s AND `objectClass` = %s `key`=%s LIMIT 1', (int)$objectId, $db->quote($objectClass), $db->quote($key) );
        $db->query($sql);

        return $this;
    }

    /**
     * getAllData
     *
     * @return \Tk\ArrayObject
     */
    public function getAllData()
    {
        $db = $this->getConfig()->getDb();
        $objectClass = $this->getClassName();
        $objectId = $this->getId();

        $sql = sprintf('SELECT * FROM `objectData` WHERE `objectId` = %s AND `objectClass` = %s ', (int)$objectId, $db->quote($objectClass) );
        $res = $db->query($sql);
        $res->setFetchMode(\PDO::FETCH_OBJ);
        $arr = new \Tk\ArrayObject();
        foreach ($res as $row) {
            $arr[$row->key] = $row->value;
        }
        return $arr;
    }

    /**
     * deleteAllData
     *
     * @return $this
     */
    public function deleteAllData()
    {
        $db = $this->getConfig()->getDb();
        $objectClass = $this->getClassName();
        $objectId = $this->getId();

        $sql = sprintf('DELETE FROM `objectData` WHERE `objectId` = %s AND `objectClass` = %s', (int)$objectId, $db->quote($objectClass) );
        $db->query($sql);

        return $this;
    }

    /**
     * The SQL needed for this trait to function
     *
     * @return string
     */
    public function getDataSql()
    {
        $sql = <<<SQL
-- --------------------------------------------------------
--
-- Table structure for table `objectData`
--
CREATE TABLE IF NOT EXISTS `objectData` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `objectId` int(10) unsigned NOT NULL DEFAULT '0',
  `objectClass` varchar(255) DEFAULT NULL,
  `key` varchar(255) DEFAULT NULL,
  `value` text,

  PRIMARY KEY (`id`),
  KEY `objectId` (`objectId`),
  KEY `key` (`key`)
) ENGINE=InnoDB;
SQL;
        return $sql;
    }
}