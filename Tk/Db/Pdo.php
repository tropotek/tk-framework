<?php
namespace Tk\Db;

use Tk\Db\Exception;

/**
 * PDO Database driver
 *
 * @author Tropotek <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @author Patrick S Scott<lazeras@kaoses.com>
 * @link http://www.kaoses.com
 * @license Copyright 2007 Tropotek
 */
class Pdo extends \PDO
{
    /**
     * The key for the option to enable ANSI mode for MySQL
     */
    const ANSI_QUOTES = 'mysql.ansi.quotes';

    /**
     * @var string
     */
    static $PARAM_QUOTE = '"';

    /**
     * @var bool
     */
    static $logLastQuery = true;

    /**
     * Variable to count the transaction int
     * @var int
     */
    protected $transactionCounter = 0;

    /**
     * The query log array.
     *
     * @var array
     */
    private $log = array();

    /**
     * The query time in seconds
     * @var int
     */
    public $queryTime = 0;

    /**
     * The total query time in seconds
     * @var int
     */
    public $totalQueryTime = 0;

    /**
     * @var string
     */
    public $lastQuery = '';

    /**
     * @var string
     */
    public $dbName = '';

    /**
     * @var string
     */
    public $driver = '';

    /**
     * @var \Closure
     */
    private $onLogListener;


    /**
     * Construct a \PDO SQL driver object
     *
     * Added options:
     *
     *  o $options[self::ANSI_QUOTES] = false; // Change to true to force MySQL to use ANSI quoting style.
     *
     *
     * @param string $dsn
     * @param string $username
     * @param string $password
     * @param array $options
     * @throws \Exception
     */
    public function __construct($dsn, $username, $password, $options = array())
    {
        if (!count($options)) {
            // Required options
            $options[\PDO::ATTR_ERRMODE] =  \PDO::ERRMODE_EXCEPTION;
        }
        parent::__construct($dsn, $username, $password, $options);
        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('\Tk\Db\PdoStatement', array($this)));

        $regs = array();
        preg_match('/^([a-z]+):(([a-z]+)=([a-z0-9_-]+))+/i', $dsn, $regs);
        $this->dbName = $regs[4];

        // Get mysql to emulate standard DB's
        if ($this->getDriver() == 'mysql') {
            $this->exec('SET CHARACTER SET utf8');
            if (isset($options['timezone'])) {
                $this->exec('SET time_zone = \'' . $options['timezone'] . '\'');
            }
            if (isset($options[self::ANSI_QUOTES]) && $options[self::ANSI_QUOTES] == true)
                $this->exec("SET SESSION sql_mode = 'ANSI_QUOTES'");
        } else {
            if (isset($options['timezone'])) {
                $this->exec('SET TIME ZONE \'' . $options['timezone'] . '\'');
            }
        }
    }

    /**
     * Helper method to create a database instance
     *
     * @param string $dbName
     * @param string $dbUser
     * @param string $dbPass
     * @param string $dbHost
     * @param string $dbType
     * @param array $options
     * @return Pdo
     */
    static function createInstance($dbName, $dbUser, $dbPass, $dbHost = 'localhost', $dbType = 'mysql', $options = array())
    {
        $dns = $dbType . ':dbname=' . $dbName . ';host=' . $dbHost;
        $obj = new self($dns, $dbUser, $dbPass, $options);

        return $obj;
    }

    /**
     * Method to return an array of connection attributes.
     *
     * @see http://www.php.net/manual/en/pdo.getattribute.php Pdo getAttribute
     *
     * @param array $attributes
     * @return array $return
     */
    function getConnectionParameters($attributes = array("DRIVER_NAME", "AUTOCOMMIT", "ERRMODE", "CLIENT_VERSION", "CONNECTION_STATUS", "PERSISTENT", "SERVER_INFO", "SERVER_VERSION"))
    {
        $return = array();
        foreach ($attributes as $val) {
            try {
                $return["PDO::ATTR_$val"] = $this->getAttribute(constant("PDO::ATTR_$val")) . "\n";
            } catch (\Exception $e) { }
        }
        return $return;
    }


    /**
     * Get the driver name
     *
     * @return string
     */
    public function getDriver()
    {
        return $this->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }


    /**
     * get the selected DB name
     *
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->dbName;
    }

    /**
     * Adds an array entry to the log.
     *
     * @param array $entry The log entry.
     */
    public function addLog(array $entry)
    {
        $this->log[] = $entry;
        if ($this->onLogListener) {
            call_user_func($this->onLogListener, $entry);
        }
    }

    /**
     * Clears the log.
     */
    public function clearLog()
    {
        $this->log = array();
    }

    /**
     * Returns the log.
     *
     * @return mixed
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * Returns the last array entry of the log
     *
     * @return mixed
     */
    public function getLastLog()
    {
        return end($this->log);
    }

    /**
     * Sets an observer on log.
     *
     * @param callable $observer The observer.
     */
    public function setOnLogListener($observer)
    {
        $this->onLogListener = $observer;
    }

    /**
     *
     * @param string $sql
     *
     * @return self
     */
    protected function setLastQuery($sql)
    {
        if (self::$logLastQuery)
            $this->lastQuery = $sql;

        return $this;
    }

    /**
     * Get the last executed query.
     *
     * @return string
     */
    public function getLastQuery()
    {
        return $this->lastQuery;
    }

    /**
     * Prepares a statement for execution and returns a statement object
     *
     * @see \PDO::prepare()
     * @see http://www.php.net/manual/en/pdo.prepare.php
     * @param $statement
     * @param array $options
     * @return  PDOStatement
     * @throws \PDOException
     */
    public function prepare($statement, $options = array())
    {
        $result = parent::prepare($statement, $options);
        return $result;
    }

    /**
     * Execute an SQL statement and return the number of affected rows
     *
     * @see \PDO::exec()
     * @see http://www.php.net/manual/en/pdo.exec.php
     * @param string $statement The SQL statement to prepare and execute
     * @return  PDOStatement
     */
    public function exec($statement)
    {
        $this->setLastQuery($statement);
        $start = microtime(true);
        $result = parent::exec($statement);
        $this->addLog(
            array(
                'query' => $statement,
                'time' => microtime(true) - $start,
                'values' => array(),
            )
        );

        return $result;
    }

    /**
     * Executes an SQL statement, returning a result set as a PDOStatement object
     *
     * @see \PDO::query()
     * @see http://au2.php.net/pdo.query
     *
     * @param string $statement
     * @param int $mode The fetch mode must be one of the PDO::FETCH_* constants.
     * @param mixed $arg3  The second and following parameters are the same as the parameters for PDOStatement::setFetchMode.
     * @return PDOStatement PDO::query returns a PDOStatement object, or FALSE on failure.
     */
    public function query($statement, $mode = PDO::ATTR_DEFAULT_FETCH_MODE, $arg3 = null)
    {
        $this->setLastQuery($statement);
        $start = microtime(true);
        $result = call_user_func_array(array('parent', 'query'), func_get_args());
        $this->addLog(
            array(
                'query' => $statement,
                'time' => microtime(true) - $start,
                'values' => array(),
            )
        );
        return $result;
    }

    /**
     *  Initiates a transaction
     *
     * @see PDO::beginTransaction()
     * @see http://php.net/manual/en/pdo.begintransaction.php#90239 SqlLite implementation
     * @see http://www.php.net/manual/en/pdo.begintransaction.php
     * @return bool
     */
    function beginTransaction()
    {
        if (!$this->transactionCounter++)
            return parent::beginTransaction();

        return $this->transactionCounter >= 0;
    }

    /**
     * Commits a transaction
     *
     * @see PDO::commit()
     * @see http://www.php.net/manual/en/pdo.commit.php
     * @return bool
     */
    function commit()
    {
        if (!--$this->transactionCounter)
            return parent::commit();

        return $this->transactionCounter >= 0;
    }

    /**
     * Rolls back a transaction
     *
     * @see PDO::rollback()
     * @see http://www.php.net/manual/en/pdo.rollback.php
     * @return bool
     */
    function rollback()
    {
        if ($this->transactionCounter >= 0) {
            $this->transactionCounter = 0;

            return parent::rollback();
        }
        $this->transactionCounter = 0;

        return false;
    }

    /**
     * Execute a query and return a result object
     *
     * @param $sql
     * @return bool
     * @throws Exception
     */
    public function multiQuery($sql)
    {
        $sql = preg_replace("(--.*)", '', $sql);
        $queryList = preg_split('/\.*;\s*\n\s*/', $sql);
        if (!is_array($queryList) || count($queryList) == 0) {
            $e = new Exception('Error in SQL query data');
            throw $e;
        }
        foreach ($queryList as $query) {
            $query = trim($query);
            if (!$query) {
                continue;
            }
            $this->exec($query);
        }
    }

    /**
     * Count a query and return the total possible results
     *
     * @param string $sql
     * @return int
     * @throws \Tk\Db\Exception
     */
    public function countFoundRows($sql = '')
    {
        if (!$sql) $sql = $this->getLastQuery();
        if (!$sql) return 0;

        self::$logLastQuery = false;
        $total = 0;
        if ($this->getDriver() == 'mysql' && preg_match('/^SELECT SQL_CALC_FOUND_ROWS/i', $sql)) {   // Mysql only
            $countSql = 'SELECT FOUND_ROWS()';
            $result = $this->query($countSql);
            if ($result === false) {
                $info = $this->errorInfo();
                throw new Exception(end($info));
            }
            $result->setFetchMode(\PDO::FETCH_ASSOC);
            $row = $result->fetch();
            if ($row) {
                $total = (int) $row['FOUND_ROWS()'];
            }
        } else if (preg_match('/^SELECT/i', $sql)) {
            $cSql = preg_replace('/(LIMIT [0-9]+(( )?,?( )?(OFFSET )?[0-9]+)?)?/i', '', $sql);
            $countSql = "SELECT COUNT(*) as i FROM ($cSql) as t";
            $result = $this->query($countSql);
            if ($result === false) {
                $info = $this->errorInfo();
                throw new Exception(end($info));
            }
            $result->setFetchMode(\PDO::FETCH_ASSOC);
            $row = $result->fetch();
            if ($row) {
                $total = (int) $row['i'];
            }
        }
        self::$logLastQuery = true;
        return $total;
    }

    /**
     * Check if a database with the supplied name exists
     *
     * @param string $dbName
     * @return bool
     */
    public function databaseExists($dbName)
    {
        $list = $this->getDatabaseList();
        return in_array($dbName, $list);
    }

    /**
     * Check if a table exists in the current database
     *
     * @param string $table
     * @return bool
     */
    public function tableExists($table)
    {
        $list = $this->getTableList();
        return in_array($table, $list);
    }
    
    /**
     * Get an array containing all the available databases to the user
     *
     * @return array
     */
    public function getDatabaseList()
    {
        $list = array();
        if ($this->getDriver() == 'mysql') {
            $sql = 'SHOW DATABASES';
            $result = $this->query($sql);
            $result->setFetchMode(\PDO::FETCH_ASSOC);
            foreach ($result as $row) {
                $list[] = $row['Database'];
            }
        } else if ($this->getDriver() == 'pgsql') {
            $sql = sprintf('SELECT datname F ROM pg_database WHERE datistemplate = false');
            $result = $this->query($sql);
            $result->setFetchMode(\PDO::FETCH_ASSOC);
            foreach ($result as $row) {
                $list[] = $row['datname'];
            }
        }
        return $list;
    }

    /**
     * Get an array containing all the table names for this DB
     *
     * @return array
     */
    public function getTableList()
    {
        $list = array();
        if ($this->getDriver() == 'mysql') {
            $sql = 'SHOW TABLES';
            $result = $this->query($sql);
            $result->setFetchMode(\PDO::FETCH_NUM);
            foreach ($result as $row) {
                $list[] = $row[0];
            }
        } else if ($this->getDriver() == 'pgsql') {
            $sql = sprintf('SELECT table_name FROM information_schema.tables WHERE table_schema = \'public\'');
            $result = $this->query($sql);
            $result->setFetchMode(\PDO::FETCH_NUM);
            foreach ($result as $row) {
                $list[] = $row[0];
            }
        }
        return $list;
    }

    /**
     * Get the insert id of the last added record.
     * Taken From: http://dev.mysql.com/doc/refman/5.0/en/innodb-auto-increment-handling.html
     *
     * @param string $table
     * @param string $pKey
     * @return int The next assigned integer to the primary key
     */
    public function getNextInsertId($table, $pKey = 'id')
    {
        if ($this->getDriver() == 'mysql') {
            $table = $this->quote($table);
            $sql = sprintf("SHOW TABLE STATUS LIKE '%s' ", $table);
            $result = $this->query($sql);
            $result->setFetchMode(\PDO::FETCH_ASSOC);
            $row = $result->fetch();
            if ($row && isset($row['Auto_increment'])) {
                return (int)$row['Auto_increment'];
            }
            $sql = sprintf("SELECT MAX(`%s`) AS `lastId` FROM `%s` ", $pKey, $table);
            $result = $this->query($sql);
            $result->setFetchMode(\PDO::FETCH_ASSOC);
            $row = $result->fetch();
            return ((int)$row['lastId']) + 1;
        } if ($this->getDriver() == 'pgsql') {
            $sql = sprintf('SELECT * FROM %s_%s_seq', $table, $pKey);
            $result = $this->prepare($sql);
            $result->execute();
            $row = $result->fetch(\PDO::FETCH_ASSOC);
            return ((int)$row['last_value']) + 1;

        }

        // Not as accurate as I would like and should not be relied upon.
        $sql = sprintf('SELECT %s FROM %s ORDER BY %s DESC LIMIT 1;', self::quoteParameter($pKey), self::quoteParameter($table), self::quoteParameter($pKey));
        $result = $this->query($sql);
        $result->setFetchMode(\PDO::FETCH_ASSOC);
        $row = $result->fetch();
        return $row[$pKey]+1;
    }

    /**
     * Encode string to avoid sql injections.
     *
     * @param string $str
     * @return string
     */
    public function escapeString($str)
    {
        if ($str) {
            return substr($this->quote($str), 1, -1);
        }
        return $str;
    }

    /**
     * @param $array
     * @return mixed
     */
    public static function quoteParameterArray($array)
    {
        foreach($array as $k => $v) {
            $array[$k] = self::quoteParameter($v);
        }
        return $array;
    }

    /**
     * Quote a parameter based on the quote system
     * if the param exists in the reserved words list
     *
     * @param string $param
     * @return string
     */
    public static function quoteParameter($param)
    {
        //if (in_array($param, self::$SQL_RESERVED_WORDS))
            return self::$PARAM_QUOTE . trim($param, self::$PARAM_QUOTE) . self::$PARAM_QUOTE;
        //return $param;
    }

}


