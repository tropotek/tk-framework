<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Session\Adapter;

/**
 * A session object
 *
 * <code>
 *    CREATE TABLE session (
 *       `id` VARCHAR(127) NOT NULL,
 *       `data` TEXT NOT NULL,
 *       `modified` DATETIME NOT NULL,
 *       `created` DATETIME NOT NULL,
 *       PRIMARY KEY (`id`)
 *   );
 *  </code>
 *
 *
 * @package Tk\Session\Adapter
 */
class Database implements Iface
{

    /**
     * @var \Tk\Db\Pdo
     */
    protected $db = null;
    protected $table = 'session';

    // Encryption
    protected $encrypt = false;

    // Session settings
    protected $sessionId = null;
    protected $written = false;


    /**
     * Create a Database session
     */
    public function __construct()
    {
        $config = \Tk\Config::getInstance();
        $this->encrypt = $config->get('session.encryption');
        if ($config->exists('session.database.table')) {
            $this->table = $config->get('session.database.table');
        }
        $this->db = $config->getDb($config->get('session.database.config'));
    }


    /**
     * Open the session
     *
     * @param string $path
     * @param string $name
     * @return bool
     */
    public function open($path, $name)
    {
        return true;
    }

    /**
     * close
     *
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * read
     *
     * @param string $id
     */
    public function read($id)
    {
        // Load the session
        $query = sprintf('SELECT * FROM %s WHERE id = %s LIMIT 1', $this->table, "'".$id."'" );
        $result = $this->db->query($query);
        $row = $result->fetch();
        if (!$row) {  // No current session
            $this->sessionId = null;
            return '';
        }
        // Set the current session id
        $this->sessionId = $id;
        // Load the data
        $data = $row->data;
        return ($this->encrypt) ? base64_decode($data) : \Tk\Encrypt::decode($data);
    }

    /**
     * write
     *
     * @param string $id
     * @param string $data
     */
    public function write($id, $data)
    {
        $result = null;
        $data = ($this->encrypt) ? base64_encode($data) : \Tk\Encrypt::encode($data);
        if ($this->sessionId === null) {
            // Insert a new session
            $query = sprintf('INSERT INTO %s VALUES (%s, %s, %s, %s)', $this->table, enquote($id), enquote($data), enquote(\Tk\Date::create()->toString()), enquote(\Tk\Date::create()->toString()));
            $result = $this->db->query($query);
        } elseif ($id === $this->sessionId) {
            // Update the existing session
            $query = sprintf("UPDATE %s SET modified = %s, data = %s WHERE id = %s", $this->table, enquote(\Tk\Date::create()->toString()), enquote($data), enquote($id));
            $result = $this->db->query($query);
        } else {
            // Update the session and id
            $query = sprintf("UPDATE %s SET id = %s, modified = %s, data = %s WHERE id = %s", $this->table, enquote($id), enquote(\Tk\Date::create()->toString()), enquote($data), enquote($this->sessionId));
            $result = $this->db->query($query);
            // Set the new session id
            $this->sessionId = $id;
        }
    }

    /**
     * destroy
     *
     * @param string $id
     */
    public function destroy($id)
    {
        $query = sprintf('DELETE FROM %s WHERE id = %s LIMIT 1', $this->table, enquote($id));
        $this->db->query($query);
        $this->sessionId = null;
        return true;
    }

    /**
     * regenerate and return new session id
     *
     * @return string
     */
    public function regenerate()
    {
        $oid = session_id();
        session_regenerate_id();
        $nid = session_id();
        $query = sprintf("UPDATE %s SET id = %s, modified = %s WHERE id = %s",
                $this->table, enquote($nid), enquote(\Tk\Date::create()->toString()),
                enquote($oid));
        $this->db->query($query);
        return $nid;
    }

    /**
     * garbage collect
     *
     * @param int $maxlifetime
     */
    public function gc($maxlifetime)
    {
        // Delete all expired sessions
        $query = sprintf('DELETE FROM %s WHERE modified < %s', $this->table, enquote(\Tk\Date::create(time() - $maxlifetime)->toString()));
        $this->db->query($query);
    }

}