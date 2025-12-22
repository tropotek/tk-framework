<?php
namespace Tk;

use Tk\Db\Exception;

/**
 *
 *
 * @phpver 8.3
 */
class Db
{
    const string DEFAULT_TIMEZONE = 'Australia/Melbourne';

    const string TABLES = 'BASE TABLE';
    const string VIEWS  = 'VIEW';

    /** enables caching of lastQuery, lastStatement and lastId */
    public static bool $CACHE_LAST = true;

    private static ?\PDO          $pdo           = null;
    private static ?DbStatement   $lastStatement = null;

	private static string $lastQuery     = '';
	private static int    $lastId        = 0;
    private static int    $transactions  = 0;     // count of transactions started to detect nested transactions
	private static array  $dsn_stack     = [];    // stack of DSNs and timezones for push/pop
	private static string $dbName        = '';

	private static string $dsn           = '';    // dsn to use when opening a connection
	private static string $timezone      = '';    // last timezone explicitly set on the db connection
    private static array  $options       = [];    // DB connection options


    /**
     * Create a Mysql SQL driver object from a dsn:
     *   - 'hostname[:port]/username/password/dbname'
     * When $options is null the default options are used from the config.php (db.mysql.options)
     */
	public static function connect(string $dsn, ?array $options = null): \PDO
    {
		assert(!empty($dsn), "no DSN for database connection");

        if (is_null($options)) {
            $options = self::getDefaultOpts();
        }

        [$host, $port, $user, $pass, self::$dbName] = array_values(self::parseDsn($dsn));
        $dbName = '';
        if (!empty(self::$dbName)) {
            $dbName = sprintf(';dbname=%s', self::$dbName);
        }

        $pdoDsn = sprintf('mysql:host=%s;port=%s;charset=utf8mb4%s', $host, $port, $dbName);
        self::$pdo = new \PDO($pdoDsn, $user, $pass, $options);
        self::$pdo->setAttribute(\PDO::ATTR_STATEMENT_CLASS, [DbStatement::class]);
        self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        self::$pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);

        self::$dsn          = $dsn;
        self::$options      = $options;
		self::$lastQuery    = '';
		self::$lastId       = 0;
		self::$transactions = 0;

        //self::$pdo->exec('SET CHARACTER SET utf8mb4;');
        //self::$pdo->exec('ALTER DATABASE CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
		if (isset(self::$options['timezone'])) {
            self::setTimezone(self::$options['timezone']);
		}

		return self::$pdo;
    }

	/**
	 * set the session timezone, which persists for the MySQL session
	 * returns the previous timezone value
	 */
	public static function setTimezone(string $timezone = 'SYSTEM'): string
	{
		$tz = self::$timezone;
		if ($timezone && $timezone != self::$timezone) {
            self::execute("SET time_zone = :timezone", compact('timezone'));
            self::$timezone = $timezone;
		}
		return $tz;
	}

    /**
     * Get the default db options from the config file
     *
     * @return array<string, mixed>
     */
    public static function getDefaultOpts(): array
    {
        $opts = Config::getValue('db.mysql.options', []);
        if (!isset($opts['timezone'])) {
            $opts['timezone'] = Config::getValue('php.date.timezone', self::DEFAULT_TIMEZONE);
        }
        return $opts;
    }


	/**
	 * remembers current DSN and timezone, connects to database with passed DSN
	 */
	public static function pushDsn(string $dsn, array $options = []): void
	{
		self::$dsn_stack[] = [self::$dsn, self::$options, self::$timezone];
		self::connect($dsn, $options);
	}

	/**
	 * pops DSN and timezone from the stack and connects to a database
	 */
	public static function popDsn(): void
	{
		assert(count(self::$dsn_stack) > 0, "no pushed DSN to pop");
		[$dsn, $options, $timezone] = array_pop(self::$dsn_stack);
		self::$timezone = $timezone;
		self::connect($dsn, $options);
	}

    public static function getDsn(): string
    {
        return self::$dsn;
    }

    public static function parseDsn(string $dsn): array
    {
        $a = explode('/', $dsn);
        $dsnArray = [
            'host'   => $a[0] ?? 'localhost',
            'port'   => 3306,
            'user'   => $a[1] ?? '',
            'pass'   => $a[2] ?? '',
            'dbName' => $a[3] ?? '',
        ];
        if (str_contains($dsnArray['host'], ':')) {
            $a = explode(':', $dsnArray['host']);
            $dsnArray['host'] = $a[0] ?? '';
            $dsnArray['port'] = intval($a[1] ?? 3306);
        }
        return $dsnArray;
    }

    /**
     * return a dsn string from an array of options:
     *
     * $options = [
     *       'host' => 'localhost',
     *       'port' => 0,
     *       'user' => 'username',
     *       'pass' => 'password',
     *       'dbName' => 'database-name',
     * ]
     */
    public static function toDsn(array $options): string
    {
        return sprintf('%s:%s/%s/%s/%s',
            $options['host'] ?? 'localhost',
            $options['port'] ?? 3306,
            $options['user'] ?? '',
            $options['pass'] ?? '',
            $options['dbName'] ?? '',
        );
    }

    public static function getPdo(): ?\PDO
    {
        return self::$pdo;
    }

    private static function setLastInsertId(int $id): void
    {
        if(self::$CACHE_LAST) {
            self::$lastId = $id;
        }
    }

    public static function getLastInsertId(): int
    {
        return self::$lastId;
    }

    private static function setLastQuery(string $query): void
    {
        if(self::$CACHE_LAST) {
            self::$lastQuery = $query;
        }
    }

    public static function getLastQuery(): string
    {
        return self::$lastQuery;
    }

    private static function setLastStatement(DbStatement $stm): void
    {
        if(self::$CACHE_LAST) {
            self::$lastStatement = $stm;
        }
    }

    public static function getLastStatement(): ?DbStatement
    {
        return self::$lastStatement;
    }

    public static function getOptions(): array
    {
        return self::$options;
    }

    public static function getDbName(): string
    {
        return self::$dbName;
    }

    /**
     * @note Only InnoDB supports the SQL statements SAVEPOINT, ROLLBACK TO SAVEPOINT,
     *       RELEASE SAVEPOINT and the optional WORK keyword for ROLLBACK.
     */
    public static function beginTransaction(): bool
    {
        if (!self::$transactions++) {
            return self::$pdo->beginTransaction();
        }
        self::execute('SAVEPOINT trans' . self::$transactions);
        return self::$transactions >= 0;
    }

    public static function commit(): bool
    {
        if (!--self::$transactions) {
            return self::$pdo->commit();
        }
        return self::$transactions >= 0;
    }

    public static function rollback(): bool
    {
        if (--self::$transactions) {
            self::execute('ROLLBACK TO trans' . (self::$transactions + 1));
            return true;
        }
        return self::$pdo->rollback();
    }

    /**
     * substitute arrays for prepared statements items
     * @param-out array $params
     */
    public static function prepareQuery(string &$query, array|object|null &$params = null): void
    {
		if (is_object($params)) $params = get_object_vars($params);
        if (!is_array($params)) return;
        // is array sequential (not assoc)
        if (array_keys($params) === range(0, count($params) - 1)) return;

        $replace = [];
        $newParams = [];
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $pieces = [];
                foreach ($value as $k => $v) {
                    $nk = "{$key}_{$k}";
                    $pieces[] = ":{$nk}";
                    $newParams[$nk] = $v;
                }
                $replace[":{$key}"] = '(' . implode(',', $pieces) . ')';
            } else {
                $newParams[$key] = $value;
            }
        }
        $query = str_replace(array_keys($replace), $replace, $query);
        $params = $newParams;
    }

    /**
     * Execute an SQL statement that doesn't get a result set
	 * e.g. update, insert, delete
	 * Returns the number of affected rows or true if succeeded, false if failed
     */
    public static function execute(string $query, array|object|null $params = null): int|bool
    {
        try {
            self::prepareQuery($query, $params);
            self::setLastQuery($query);

            /** @var DbStatement $stm */
            $stm = self::$pdo->prepare($query);
            $stm->execute($params);
            self::setLastStatement($stm);

            if (!empty(self::$pdo->lastInsertId())) {
                self::setLastInsertId(intval(self::$pdo->lastInsertId()));
            }

            return $stm->rowCount();
        } catch (\Exception $e) {
            Log::error($e->__toString());
            throw new Exception($e->getMessage(), $e->getCode(), $query, $params);
        }
    }

    /**
     * Executes an SQL statement, returning a result set as a PDOStatement object
     *
	 * @template T of object
	 * @param class-string<T> $classname
	 * @return array<int,T>
     */
    public static function query(string $query, array|object|null $params = null, string $classname = 'stdClass'): array
    {
        try {
            self::prepareQuery($query, $params);
            self::setLastQuery($query);

            /** @var DbStatement $stm */
            $stm = self::$pdo->prepare($query);
            $stm->execute($params);
            self::setLastStatement($stm);

            $rows = [];
            while ($row = $stm->fetchMappedObject($classname)) {
                /** @var T $row */
                $rows[] = $row;
            }
            return $rows;
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $query, $params);
        }
    }

	/**
	 * execute sql SELECT statement
	 * returns first row as an object of type $classname, or null if no rows found
	 *
	 * @template T of object
	 * @param class-string<T> $classname
	 * @return T|null
	 */
	public static function queryOne(string $query, array|object|null $params = null, string $classname = 'stdClass'): ?object
	{
        try {
            self::prepareQuery($query, $params);
            self::setLastQuery($query);

            /** @var DbStatement $stm */
            $stm = self::$pdo->prepare($query);
            $stm->execute($params);
            self::setLastStatement($stm);

            /** @var T|null $row */
            $row = $stm->fetchMappedObject($classname);
            return (false === $row) ? null : $row;
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $query, $params);
        }
	}

	/**
	 * execute sql SELECT statement
	 * returns string representation of the first value of the first row of the result
	 * or null if nothing selected
	 */
	public static function queryVal(string $query, array|object|null $params = null): mixed
	{
        try {
            self::prepareQuery($query, $params);
            self::setLastQuery($query);

            /** @var DbStatement $stm */
            $stm = self::$pdo->prepare($query);
            $stm->execute($params);
            self::setLastStatement($stm);

            return $stm->fetchColumn();
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $query, $params);
        }
	}

    /**
     * query a single value, always convert to int
     */
    public static function queryInt(string $query, array|object|null $params = null): int
    {
        $v = self::queryVal($query, $params);
        return intval($v);
    }

    /**
     * query a single value, always convert to string
     * returns empty string for null
     */
    public static function queryString(string $query, array|object|null $params = null, string $nullvalue = ''): string
    {
        $v = self::queryVal($query, $params);
        return strval($v ?? $nullvalue);
    }

    /**
     * query a single value, always convert to float
     */
    public static function queryFloat(string $query, array|object|null $params = null): float
    {
        $v = self::queryVal($query, $params);
        return floatval($v);
    }

    /**
     * query a single value, always convert to bool
     */
    public static function queryBool(string $query, array|object|null $params = null): bool
    {
        $v = self::queryVal($query, $params);
        return boolval($v);
    }

    /**
     * execute SQL SELECT statement
     * returns lookup table as [row[key] => row[value], ...] for each fetched row
     * where $key and $value are column names retrieved by the query
     * if $key is empty, the returned list is a plain array of [value, value, ...]
     *
     * @template T of object
     * @param class-string<T> $classname
     * @deprecated use array_column($list, $valueProp, $keyProp);
     */
    public static function queryList(string $sql, string $key, string $value, array|object|null $params = null, string $classname = 'stdClass'): array
    {
        $rows = self::query($sql, $params, $classname);

        $list = [];
        if ($key) {
            foreach ($rows as $row) {
                $list[$row->$key] = $row->$value;
            }
        } else {
            foreach ($rows as $row) {
                $list[] = $row->$value;
            }
        }

        return $list;
    }

    /**
     * execute SQL SELECT statement
     * returns an array of [key => row, ...]
     * where the key is a column returned in the query results
     *
     * @template T of object
     * @param class-string<T> $classname
     * @deprecated use array_column($list, null, $keyProp);
     */
    public static function queryAssoc(string $sql, string $key, array|object|null $params = null, string $classname = 'stdClass'): array
    {
        $rows = self::query($sql, $params, $classname);
        $list = [];
        foreach ($rows as $row) {
            $list[$row->$key] = $row;
        }

        return $list;
    }

	/**
	 * convenience function: insert a row
	 * $values is [column => value, ...] or an object
	 */
	public static function insert(string $table, array|object $values): int|bool
	{
		if (is_object($values)) $values = get_object_vars($values);
		$cols = implode(', ', array_keys($values));
		$vals = preg_replace('/([^, ]+)/', ':$1', $cols);
		$cols = implode(', ', array_map(fn($r) => '`'.$r.'`', array_keys($values)));
		return self::execute("INSERT INTO $table ($cols) VALUES ($vals)", $values);
	}

	/**
	 * convenience function: insert a row ignoring errors
	 * $values is [column => value, ...] or an object
	 */
	public static function insertIgnore(string $table, array|object $values): int|bool
	{
		if (is_object($values)) $values = get_object_vars($values);
		$cols = implode(', ', array_keys($values));
		$vals = preg_replace('/([^, ]+)/', ':$1', $cols);
        $cols = implode(', ', array_map(fn($r) => '`'.$r.'`', array_keys($values)));
		return self::execute("INSERT IGNORE INTO $table ($cols) VALUES ($vals)", $values);
	}

	/**
	 * convenience function: delete a row matching all conditions
	 * $values is [column => value, ...] or an object
	 * $values must contain at least one condition
	 */
	public static function delete(string $table, array|object $values): int|bool
    {
		if (is_object($values)) $values = get_object_vars($values);
        $where = [];
		foreach (array_keys($values) as $col) {
			$where[] = "`$col` = :$col";
        }
		$where = implode(' AND ', $where);

        return self::execute("DELETE FROM $table WHERE $where", $values);
    }

	/**
	 * convenience function: update a row
	 * $primaryKey is the name of the primary key column, must be in $values.
	 * $values is [column => value, ...] or an object
	 *
	 * note: cannot change primary key with this function
	 */
	public static function update(string $table, string $primaryKey, array|object $values): int|bool
	{
		if (is_object($values)) $values = get_object_vars($values);

		$set = [];
		$vals = [];
		foreach ($values as $column => $value) {
			if ($column != $primaryKey) {
				$set[] = "`$column` = :$column";
			}
			$vals[$column] = $value;
		}

		$set = implode(', ', $set);
		$sql = "UPDATE $table SET $set WHERE `$primaryKey` = :$primaryKey";
		return self::execute($sql, $vals);
	}

	/**
	 * insert (cols) values (vals) on duplicate key update col=val, ...
	 * $primaryKey is not updated
	 * values is [column => value, ...] or an object
	 */
	public static function insertUpdate(string $table, string $primaryKey, array|object $values): int|bool
	{
		if (is_object($values)) $values = get_object_vars($values);
		if (empty($values[$primaryKey])) $values[$primaryKey] = null;
		$cols = implode(', ', array_keys($values));
		$vals = preg_replace('/([^, ]+)/', ':$1', $cols);

		$set = [];
		foreach (array_keys($values) as $column) {
			if ($column != $primaryKey) {
				$set[] = "`$column` = :$column";
			}
		}

		$set = implode(', ', $set);

		if ($set) {
			$sql = "INSERT INTO {$table} ({$cols}) VALUES ({$vals}) ON DUPLICATE KEY UPDATE {$set}";
		} else {
			// only got primary key column, insert ignore new row
			$sql = "INSERT IGNORE INTO $table ($cols) VALUES ($vals)";
		}

		return self::execute($sql, $values);
	}

    /**
     * Predict the next insert ID of the table
     * Taken From: http://dev.mysql.com/doc/refman/5.0/en/innodb-auto-increment-handling.html
     */
    public static function getNextInsertId(string $table): int
    {
        $stm = self::$pdo->prepare("SELECT AUTO_INCREMENT FROM information_schema.tables WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table");
        $stm->execute(compact('table'));
        return intval($stm->fetchColumn());
    }

    /**
     * Return an array with [limit, offset, total] values for a query
     */
    public static function countTotalRows(string $sql, ?array $params = null): array
    {
        $sql = trim($sql);
        if (!$sql) return [0, 0, 0];
        if (stripos($sql, 'SELECT ') !== 0) return [0, 0, 0];

        $limit = 0;
        $offset = 0;
        $total = 0;
        $cSql = $sql;   // query without limit/offset
        if (preg_match('/(.*)?(LIMIT\s([0-9]+)((\s+OFFSET\s)?|(,\s?)?)([0-9]+)?)+$/is', trim($sql), $match)) {
            $cSql = trim($match[1]);
            $limit = (int)($match[3]);
            $offset = (int)($match[7] ?? 0);
        }

        $countSql = "SELECT COUNT(*) as i FROM ($cSql) as t";
        $stm = self::$pdo->prepare($countSql);
        if (false === $stm->execute($params)) {
            $info = self::$pdo->errorInfo();
            throw new Exception(end($info));
        }
        $stm->setFetchMode(\PDO::FETCH_ASSOC);
        $row = $stm->fetch();
        if ($row) $total = (int) $row['i'];
        return [$limit, $offset, $total];
    }

    /**
     * return
     */
    public static function getTableInfo(string $table, bool $camelKeys = false): array
    {
        $types = [
            'varchar'    => 'string',
            'longtext'   => 'string',
            'enum'       => 'string',
            'set'        => 'string',
            'text'       => 'string',
            'tinytext'   => 'string',
            'char'       => 'string',
            'mediumtext' => 'string',
            'blob'       => 'string',
            'varbinary'  => 'string',
            'binary'     => 'string',
            'longblob'   => 'string',
            'tinyblob'   => 'string',
            'bool'       => 'bool',
            'bigint'     => 'int',
            'int'        => 'int',
            'tinyint'    => 'int',
            'smallint'   => 'int',
            'mediumint'  => 'int',
            'decimal'    => 'float',
            'float'      => 'float',
            'double'     => 'float',
            'datetime'   => 'timestamp',
            'timestamp'  => 'datetime',
            'date'       => 'date',
            'time'       => 'time',
            'year'       => 'year',
        ];
        $table = self::escapeTable($table);

        $query = "DESCRIBE `$table`";
        try {
            $list = [];
            $stm = self::$pdo->prepare($query);
            $stm->execute();
            $stm->setFetchMode(\PDO::FETCH_ASSOC);

            foreach ($stm as $row) {
                $col = (object)$row;
                $col->name = $col->Field;
                $col->name_camel = lcfirst(str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $col->name))));
                preg_match('/^([a-z0-9_]+)(\(([0-9]+)\))?(.+)?/i', $col->Type, $r);

                $col->mysql_type = $r[1] ?? 'varchar';
                $col->len = intval($r[3] ?? 0);
                $col->ext = trim($r[4] ?? '');
                $col->php_type = $types[$col->mysql_type] ?? 'string';
                if ($col->php_type == 'int' && $col->len == 1) $col->php_type = 'bool';

                $col->is_primary_key = $col->Key == 'PRI';
                $col->is_unique      = in_array($col->Key, ['PRI', 'UNI']);
                $col->is_numeric     = in_array($col->php_type, ['int', 'tinyint', 'decimal', 'float']);
                $col->is_string      = in_array($col->php_type, ['string', 'timestamp', 'datetime', 'date', 'time', 'year']);
                $col->is_datetime    = in_array($col->php_type, ['timestamp', 'datetime', 'date', 'time', 'year']);
                $col->is_enum        = ($col->mysql_type == 'enum');
                $col->is_set         = ($col->mysql_type == 'set');

                $key = $camelKeys ? $col->name_camel : $col->name;
                $list[$key] = $col;
            }
            return $list;
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $query);
        }
    }

    public static function getDatabaseList(): array
    {
        $stm = self::$pdo->prepare("SHOW DATABASES");
        $stm->execute();
        return $stm->fetchAll(\PDO::FETCH_COLUMN, 0);
    }

    public static function databaseExists(string $dbName): bool
    {
        $val = self::queryVal("SHOW DATABASES LIKE :dbName", compact('dbName'));
        return $val == $dbName;
    }

    /**
     * set the $type to restrict a list (self::TABLES or self::VIEWS)
     * (default) returns both
     */
    public static function getTableList(?string $type = null): array
    {
        $query = "SHOW TABLES";
        if (!is_null($type) && in_array($type, [self::TABLES, self::VIEWS])) {
            $query = "SHOW FULL TABLES WHERE Table_type = '$type'";
        }
        $stm = self::$pdo->prepare($query);
        $stm->execute();
        return $stm->fetchAll(\PDO::FETCH_COLUMN, 0);
    }

    public static function tableExists(string $table): bool
    {
        $val = self::queryVal("SHOW TABLES LIKE :table", compact('table'));
        return $val == $table;
    }

    public static function dropTable(string $tableName): int
    {
        $tableName = self::escapeTable($tableName);
        if (!self::tableExists($tableName)) return 0;
        $query = "SET FOREIGN_KEY_CHECKS = 0;SET UNIQUE_CHECKS = 0;\n";
        $query .= "DROP TABLE IF EXISTS `{$tableName}` CASCADE;\n";
        $query .= "SET FOREIGN_KEY_CHECKS = 1;SET UNIQUE_CHECKS = 1;";
        $stm = self::$pdo->prepare($query);
        $stm->execute();
        return $stm->rowCount();
    }

    /**
     * Remove all tables from a DB
     * You must send $confirm = true to ensure correct execution
     */
    public static function dropAllTables(bool $confirm = false, array $exclude = []): int
    {
        if (!$confirm) return 0;
        $query = "SET FOREIGN_KEY_CHECKS = 0;SET UNIQUE_CHECKS = 0;\n";
        foreach (self::getTableList() as $v) {
            if (in_array($v, $exclude)) continue;
            $query .= "DROP TABLE IF EXISTS `{$v}` CASCADE;\n";
        }
        $query .= "SET FOREIGN_KEY_CHECKS = 1;SET UNIQUE_CHECKS = 1;";
        $stm = self::$pdo->prepare($query);
        $stm->execute();

        return $stm->rowCount();
    }

    /**
     * sanitize a table/Db name for queries
     */
    public static function escapeTable(string $table): string
    {
        return preg_replace('/[^a-z0-9_]/i', '', $table);
    }
}
