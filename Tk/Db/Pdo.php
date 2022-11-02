<?php
namespace Tk\Db;


/**
 * PDO Database driver
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class Pdo extends \PDO
{

    /**
     * The key for the option to enable ANSI mode for MySQL
     */
    const ANSI_QUOTES = 'mysql.ansi.quotes';


    private static array $_INSTANCE = [];

    /**
     * Default timeout value in seconds for communications with the database.
     */
    public static int $PDO_TIMEOUT = 30;

    /**
     * Enable/disable the last query log
     */
    public static bool $Q_LOG = true;


    protected string $parameterQuote = '';

    protected int $transactionCounter = 0;

    public string $lastQuery = '';

    public string $dbName = '';

    public string $driver = '';

    private array $options;


    /**
     * Construct a \PDO SQL driver object
     *
     * Added options:
     *
     *  o $options['mysql.ansi.quotes'] = true; // Change to true to force MySQL to use ANSI quoting style.
     *  o $options['timezone'] = '';
     *
     * @param string $dsn
     * @param string $username
     * @param string $password
     * @param array $options
     * @throws \Exception
     */
    public function __construct(string $dsn, string $username, string $password, array $options = [])
    {
        parent::__construct($dsn, $username, $password, $options);
        $this->options = $options;
        $this->options['user'] = $username;
        $this->options['pass'] = $password;
        $this->driver = $this->options['type'];

        //$this->setAttribute(\PDO::ATTR_STATEMENT_CLASS, [\Tk\Db\PdoStatement::class, [$this]]); // Not compat with PHP 5.3
        $this->setAttribute(\PDO::ATTR_STATEMENT_CLASS, ['\Tk\Db\PdoStatement', [$this]]);

        $regs = [];
        preg_match('/^([a-z]+):(([a-z]+)=([a-z0-9_-]+))+/i', $dsn, $regs);
        $this->dbName = $regs[4];

        $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->setAttribute(\PDO::ATTR_TIMEOUT, self::$PDO_TIMEOUT);

        // Get mysql to emulate standard DB's
        if ($this->getDriver() == 'mysql') {
            $version = $this->query('select version()')->fetchColumn();
            $version = (float)mb_substr($version, 0, 6);
            if ($version < '5.5.3') {
                $this->exec('SET CHARACTER SET utf8;');
                $this->exec('ALTER DATABASE CHARACTER SET utf8 COLLATE utf8_unicode_ci;');
            } else {
                $this->exec('SET CHARACTER SET utf8mb4;');
                $this->exec('ALTER DATABASE CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
            }

            if (isset($options['timezone'])) {  // TODO: Check this as it does not seem to work
                $this->exec('SET time_zone = \'' . $options['timezone'] . '\'');
            }
            if (isset($options[self::ANSI_QUOTES]) && $options[self::ANSI_QUOTES] == true) {
                $this->exec("SET SESSION sql_mode = 'ANSI_QUOTES'");
            }
            $this->parameterQuote = '`';
        } else {
            if (isset($options['timezone'])) {
                $this->exec('SET TIME ZONE \'' . $options['timezone'] . '\'');
            }
            $this->parameterQuote = '"';
        }
    }

    /**
     * Call this to create/get a DB instance
     *
     * $options = [
     *   'type' => 'mysql',
     *   'host' => 'localhost',
     *   'port' => '3306',
     *   'name' => 'database',
     *   'user' => 'user',
     *   'pass' => 'pass',
     *   'timezone' => '',              // optional
     *   'mysql.ansi.quotes' => true    // optional
     * ];
     *
     * Different database instances are stored in an array by the $name key
     *
     * ON the first call supply the connection params in the options as
     * outlined, then subsequent calls can be made with no params or just the name
     * param as required.
     *
     * When calling this if only the options array is sent in place of the name value
     * then the 'default' value is used for the name, therefore:
     *   Pdo::getInstance($options) is a valid call
     *
     */
    public static function instance(string $name, array $options = []): ?Pdo
    {
        // return the first available DB connection if no params
        if (!$name && !count($options) && count(self::$_INSTANCE)) {
            return current(self::$_INSTANCE);
        }
        if (!isset(self::$_INSTANCE[$name])) {
            self::$_INSTANCE[$name] = static::create($options);
            return self::$_INSTANCE[$name];
        }
        return self::$_INSTANCE[$name];
    }

    /**
     * Call this to create a new DB instance
     *
     * $options = [
     *   'type' => 'mysql',
     *   'host' => 'localhost',
     *   'port' => '3306',
     *   'name' => 'database',
     *   'user' => 'user',
     *   'pass' => 'pass',
     *   'timezone' => '',              // optional
     *   'mysql.ansi.quotes' => true    // optional
     * ];
     *
     */
    public static function create(array $options): Pdo
    {
        $dsn = $options['type'] . ':dbname=' . $options['name'];
        if (isset($options['host'])) $dsn .= ';host=' . $options['host'];
        if (isset($options['port'])) $dsn .= ';port=' . $options['port'];
        $db = new static($dsn, $options['user'], $options['pass'], $options);
        return $db;
    }


    /**
     * Method to return an array of connection attributes.
     *
     * @see http://www.php.net/manual/en/pdo.getattribute.php Pdo getAttribute
     */
    public function getConnectionParameters(array $attributes = ["DRIVER_NAME", "AUTOCOMMIT", "ERRMODE", "CLIENT_VERSION",
        "CONNECTION_STATUS", "PERSISTENT", "SERVER_INFO", "SERVER_VERSION"]): array
    {
        $return = [];
        foreach ($attributes as $val) {
            try {
                $return["PDO::ATTR_$val"] = $this->getAttribute(constant("PDO::ATTR_$val")) . "\n";
            } catch (\Exception $e) { }
        }
        return $return;
    }

    /**
     * Return an option that was sent to the DB on creation
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Get the driver name
     */
    public function getDriver(): string
    {
        return $this->driver;
    }

    /**
     * get the selected DB name
     */
    public function getDatabaseName(): string
    {
        return $this->dbName;
    }

    public function setLastQuery(string $sql): Pdo
    {
        if (self::$Q_LOG)
            $this->lastQuery = $sql;

        return $this;
    }

    /**
     * Get the last executed query.
     */
    public function getLastQuery(): string
    {
        return $this->lastQuery;
    }

    /**
     * Prepares a statement for execution and returns a statement object
     *
     * @param string $query
     * @param array $options
     * @return  \PDOStatement|false
     * @throws \PDOException
     * @see \PDO::prepare()
     * @see http://www.php.net/manual/en/pdo.prepare.php
     */
    public function prepare(string $query, array $options = []): \PDOStatement|false
    {
        $result = parent::prepare($query, $options);
        return $result;
    }

    /**
     * Execute an SQL statement and return the number of affected rows
     *
     * @param string $query The SQL statement to execute
     * @return int|false
     * @throws Exception
     * @see http://www.php.net/manual/en/pdo.exec.php
     */
    public function exec(string $query): int|false
    {
        $this->setLastQuery($query);

        try {
            $result = parent::exec($query);
        } catch (\Exception $e) {
            $info = $this->errorInfo();
            throw new Exception(end($info), $e->getCode(), $e, $query);
        }

        if ($result === false) {
            $info = $this->errorInfo();
            throw new Exception(end($info), $this->errorCode(), null, $query);
        }

        return $result;
    }

    /**
     * @param string $query
     * @param int $mode
     * @param null $arg3
     * @param array $ctorargs
     * @return mixed
     */
    public function tkQuery($query, $mode = PDO::ATTR_DEFAULT_FETCH_MODE, $arg3 = null, $ctorargs = [])
    {
        return call_user_func_array(array($this, 'query'), func_get_args());
    }


    /**
     * Executes an SQL statement, returning a result set as a PDOStatement object
     *
     * @param string $query
     * @param int $mode The fetch mode must be one of the PDO::FETCH_* constants.
     * @param mixed $arg3 The second and following parameters are the same as the parameters for PDOStatement::setFetchMode.
     * @param array $ctorargs
     * @return PDOStatement \PDO::query() returns a PDOStatement object, or FALSE on failure.
     * @throws Exception
     */
    public function query($query, $mode = PDO::ATTR_DEFAULT_FETCH_MODE, ...$fetchModeArgs): \PDOStatement|false
    {
        $this->setLastQuery($query);
        try {
            $result = call_user_func_array(array('parent', 'query'), func_get_args());
            if ($result === false) {
                $info = $this->errorInfo();
                throw new Exception(end($info), $this->errorCode(), null, $query);
            }
        } catch (\Exception $e) {
            $info = $this->errorInfo();
            throw new Exception(end($info), $e->getCode(), $e, $query);
        }
        return $result;
    }

    /**
     *  Initiates a transaction
     *
     * @see PDO::beginTransaction()
     * @see http://php.net/manual/en/pdo.begintransaction.php#90239 SqlLite implementation
     * @see http://www.php.net/manual/en/pdo.begintransaction.php
     */
    public function beginTransaction(): bool
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
     */
    public function commit(): bool
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
     */
    public function rollback(): bool
    {
        if ($this->transactionCounter >= 0) {
            $this->transactionCounter = 0;
            return parent::rollBack();
        }
        $this->transactionCounter = 0;
        return false;
    }


    /**
     * Count a query and return the total possible results
     *
     * @param string $sql
     */
    public function countFoundRows(string $sql = ''): int
    {
        if (!$sql) $sql = $this->getLastQuery();
        if (!$sql) return 0;

        self::$Q_LOG = false;
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
        self::$Q_LOG = true;
        return $total;
    }

    /**
     * Check if a database with the supplied name exists
     * @throws Exception
     */
    public function hasDatabase(string $dbName): bool
    {
        $list = $this->getDatabaseList();
        return in_array($dbName, $list);
    }

    /**
     * Get an array containing all the available databases to the user
     *
     * @throws Exception
     */
    public function getDatabaseList(): array
    {
        $result = null;
        $list = [];
        if ($this->getDriver() == 'mysql') {
            $sql = 'SHOW DATABASES';
            $result = $this->query($sql);
        } else if ($this->getDriver() == 'pgsql') {
            $sql = sprintf('SELECT datname FROM pg_database WHERE datistemplate = false');
            $result = $this->query($sql);
        }
        if ($result) {
            $list = $result->fetchAll(\PDO::FETCH_COLUMN, 0);
        }
        return $list;
    }

    /**
     * Check if a table exists in the current database
     *
     * @throws Exception
     */
    public function hasTable(string $table): bool
    {
        $list = $this->getTableList();
        return in_array($table, $list);
    }

    /**
     * Get an array containing all the table names for this DB
     *
     * @throws Exception
     */
    public function getTableList(): array
    {
        self::$Q_LOG = false;
        $result = null;
        $list = [];
        if ($this->getDriver() == 'mysql') {
            $sql = 'SHOW TABLES';
            $result = $this->query($sql);
        } else if ($this->getDriver() == 'pgsql') {
            $sql = sprintf('SELECT table_name FROM information_schema.tables WHERE table_schema = \'public\'');
            $result = $this->query($sql);
        }
        if ($result) {
            $list = $result->fetchAll(\PDO::FETCH_COLUMN, 0);
        }
        self::$Q_LOG = true;
        return $list;
    }

    /**
     * Get an array containing all the table names for this DB
     *
     * @throws Exception
     */
    public function getTableInfo(string $table): array
    {
        self::$Q_LOG = false;
        $list = [];
        $result = null;
        if ($this->getDriver() == 'mysql') {
            $sql = sprintf('DESCRIBE %s ', $this->quoteParameter($table));
            $result = $this->query($sql);
            if ($result) {
                $result->setFetchMode(\PDO::FETCH_ASSOC);
                foreach ($result as $row) {
                    $list[$row['Field']] = $row;
                }
            }
        } else if ($this->getDriver() == 'pgsql') { // Try to emulate the mysql DESCRIBE as close as possible
            $sql = sprintf('select * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name =  %s', $this->quote($table));
            $result = $this->query($sql);
            $result->setFetchMode(\PDO::FETCH_ASSOC);
            foreach ($result as $row) {
                $list[$row['column_name']] = array(
                    'Field' => $row['column_name'],
                    'Type' => $row['data_type'],
                    'Null' => $row['is_nullable'],
                    'Key' => '',
                    'Default' => $row['column_default'],
                    'Extra' => ''
                );
                if (preg_match('/^nextval\(/', $row['column_default'])) {
                    $list[$row['column_name']]['Key'] = 'PRI';
                    $list[$row['column_name']]['Extra'] = 'auto_increment';
                }
            }
            $list = array_reverse($list);
        }
        self::$Q_LOG = true;
        return $list;
    }

    /**
     * drop a specific table
     *
     * @throws Exception
     */
    public function dropTable(string $tableName): bool
    {
        if (!$this->hasTable($tableName)) return false;
        $sql = '';
        if ($this->getDriver() == 'mysql') {
            $sql .= sprintf('SET FOREIGN_KEY_CHECKS = 0;SET UNIQUE_CHECKS = 0;');
        }
        $sql .= sprintf('DROP TABLE IF EXISTS %s CASCADE;', $this->quoteParameter($tableName));
        if ($this->getDriver() == 'mysql') {
            $sql .= sprintf('SET FOREIGN_KEY_CHECKS = 1;SET UNIQUE_CHECKS = 1;');
        }
        $this->exec($sql);
        return true;
    }

    /**
     * Remove all tables from a DB
     * You must send true as a parameter to ensure it executes
     *
     * @throws Exception
     */
    public function dropAllTables(bool $confirm = false, array $exclude = []): bool
    {
        if (!$confirm) return false;
        $sql = '';
        if ($this->getDriver() == 'mysql') {
            $sql .= sprintf('SET FOREIGN_KEY_CHECKS = 0;SET UNIQUE_CHECKS = 0;');
        }
        foreach ($this->getTableList() as $i => $v) {
            if (in_array($v, $exclude)) continue;
            $sql .= sprintf('DROP TABLE IF EXISTS %s CASCADE;', $this->quoteParameter($v));
        }
        if ($this->getDriver() == 'mysql') {
            $sql .= sprintf('SET FOREIGN_KEY_CHECKS = 1;SET UNIQUE_CHECKS = 1;');
        }
        $this->exec($sql);
        return true;
    }

    /**
     * Get the insert id of the last added record.
     * Taken From: http://dev.mysql.com/doc/refman/5.0/en/innodb-auto-increment-handling.html
     *
     * @throws Exception
     */
    public function getNextInsertId(string $table, string $pKey = 'id'): int
    {
        self::$Q_LOG = false;
        if ($this->getDriver() == 'mysql') {
            $table = $this->quote($table);
            $sql = sprintf('SHOW TABLE STATUS LIKE %s ', $table);
            $result = $this->query($sql);
            $result->setFetchMode(\PDO::FETCH_ASSOC);
            $row = $result->fetch();
            if ($row && isset($row['Auto_increment'])) {
                return (int)$row['Auto_increment'];
            }
            $sql = sprintf('SELECT MAX(`%s`) AS `lastId` FROM `%s` ', $pKey, $table);
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
        self::$Q_LOG = true;
        return $row[$pKey]+1;
    }

    /**
     * Encode string to avoid sql injections.
     */
    public function escapeString(string $str): string
    {
        if ($str) {
            return substr($this->quote($str), 1, -1);
        }
        return $str;
    }

    /**
     *
     */
    public function quoteParameterArray(array $array): array
    {
        foreach($array as $k => $v) {
            $array[$k] = $this->quoteParameter($v);
        }
        return $array;
    }

    /**
     * Quote a parameter based on the quote system
     * if the param exists in the reserved words list
     */
    public function quoteParameter(string $param): string
    {
        return $this->parameterQuote . trim($param, $this->parameterQuote) . $this->parameterQuote;
    }

}


