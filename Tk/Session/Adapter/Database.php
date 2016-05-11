<?php
namespace Tk\Session\Adapter;

/**
 * A PDO DB session object
 *
 * <code>
 *    CREATE TABLE session (
 *       session_id VARCHAR(127) NOT NULL,
 *       data TEXT NOT NULL,
 *       modified DATETIME NOT NULL,
 *       created DATETIME NOT NULL,
 *       PRIMARY KEY (sessionId)
 *   );
 *  </code>
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Database implements Iface
{

    /**
     * @var \Tk\Db\Pdo
     */
    protected $db = null;
    
    /**
     * @var mixed|string
     */
    protected $table = 'session';

    /**
     * @var bool|mixed
     */
    protected $encrypt = false;

    /**
     * @var string
     */
    private $sessionId = null;
    

    /**
     * Create a Database session adaptor.
     *
     * @param \Tk\Db\Pdo $db
     * @param string $table
     * @param bool $encrypt
     */
    public function __construct(\Tk\Db\Pdo $db, $table = 'session', $encrypt = false)
    {
        $this->db = $db;
        $this->table = $table;
        $this->encrypt = $encrypt;
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
     * Use this to put creation in one place.
     * 
     * 
     * @param string $time
     * @param null $timezone
     * @return \DateTime
     */
    private function createDate($time = 'now', $timezone = null)
    {
        return \Tk\Date::create($time, $timezone);
    }

    /**
     * read
     *
     * @param string $id
     * @return string
     */
    public function read($id)
    {
        // Load the session
        $query = sprintf('SELECT * FROM %s WHERE session_id = %s LIMIT 1', $this->db->quoteParameter($this->table), $this->db->quote($id));
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
     * @return bool
     */
    public function write($id, $data)
    {
        $data = ($this->encrypt) ? base64_encode($data) : \Tk\Encrypt::encode($data);
        if ($this->sessionId === null) {
            // Insert a new session
            $query = sprintf('INSERT INTO %s VALUES (%s, %s, %s, %s)', 
                $this->table, $this->db->quote($id), $this->db->quote($data), $this->db->quote($this->createDate()->format(\Tk\Date::ISO_DATE)), $this->db->quote($this->createDate()->format(\Tk\Date::ISO_DATE)));
            $this->db->query($query);
        } elseif ($id === $this->sessionId) {
            // Update the existing session
            $query = sprintf("UPDATE %s SET modified = %s, data = %s WHERE session_id = %s", 
                $this->table, $this->db->quote($this->createDate()->format(\Tk\Date::ISO_DATE)), $this->db->quote($data), $this->db->quote($id));
            $this->db->query($query);
        } else {
            // Update the session and id
            $query = sprintf("UPDATE %s SET session_id = %s, modified = %s, data = %s WHERE session_id = %s", 
                $this->table, $this->db->quote($id), $this->db->quote($this->createDate()->format(\Tk\Date::ISO_DATE)), $this->db->quote($data), $this->db->quote($this->sessionId));
            $this->db->query($query);
            // Set the new session id
            $this->sessionId = $id;
        }
        return true;
    }

    /**
     * destroy
     *
     * @param string $id
     * @return bool
     */
    public function destroy($id)
    {
        $query = sprintf('DELETE FROM %s WHERE session_id = %s LIMIT 1', $this->table, $this->db->quote($id));
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
        $query = sprintf("UPDATE %s SET session_id = %s, modified = %s WHERE id = %s",
                $this->table, $this->db->quote($nid), $this->db->quote($this->createDate()->format(\Tk\Date::ISO_DATE)),
                $this->db->quote($oid));
        $this->db->query($query);
        return $nid;
    }

    /**
     * garbage collect
     *
     * @param int $maxlifetime
     * @return bool
     */
    public function gc($maxlifetime)
    {
        // Delete all expired sessions
        $query = sprintf('DELETE FROM %s WHERE modified < %s', $this->table, $this->db->quote($this->createDate(time() - $maxlifetime)->format(\Tk\Date::ISO_DATE)));
        $this->db->query($query);
        return true;
    }

}