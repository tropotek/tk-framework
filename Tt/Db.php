<?php
namespace Tt;

use Tk\Db\Exception;

/**
 * @phpver 8.1
 */
class Db
{
    public static bool $LOG = true;

    private \PDO           $pdo;
    private ?\PDOStatement $lastStatement   = null;
	private string         $lastQuery       = '';
	private int            $lastId          = 0;

	private string  $dsn              = '';    // dsn to use when opening connection
	private string  $timezone         = '';    // last timezone explicitly set on the db connection
    private array   $options          = [];    // DB connection options
    private int     $transactions     = 0;     // count of transactions started to detect nested transactions
	private string  $dbName           = '';

    /**
     * Create a Mysql SQL driver object from a dsn:
     *   - 'hostname[:port]/username/password/dbname'
     */
    public function __construct(string $dsn, array $options = [])
    {
        $this->dsn = $dsn;
        [$host, $port, $user, $pass, $this->dbName] = array_values(self::parseDsn($dsn));

        $pdoDsn = sprintf('mysql:host=%s;port=%s;charset=utf8mb4;dbname=%s', $host, $port, $this->dbName);
        $this->pdo = new \PDO($pdoDsn, $user, $pass, $options);
        $this->pdo->setAttribute(\PDO::ATTR_STATEMENT_CLASS, [DbStatement::class, [$this]]);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);
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

    public function getPdo(): \PDO
    {
        return $this->pdo;
    }

    public function lastInsertId(): int
    {
        return $this->lastId;
    }

    public function getLastQuery(): string
    {
        return $this->lastQuery;
    }

    public function getLastStatement(): \PDOStatement
    {
        return $this->lastStatement;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getDbName(): string
    {
        return $this->dbName;
    }

	/**
	 * set the session timezone, which persists for the MySQL session
	 * returns the previous timezone value
	 */
	public function setTimezone(string $timezone='SYSTEM'): string
	{
		$tz = $this->timezone;
		if ($timezone && $timezone != $this->timezone) {
            $this->execute("SET time_zone = :timezone", compact('timezone'));
            $this->timezone = $timezone;
		}
		return $tz;
	}

    public function beginTransaction(): bool
    {
        if (!$this->transactions++) {
            return $this->getPdo()->beginTransaction();
        }
        $this->execute('SAVEPOINT trans' . $this->transactions);
        return $this->transactions >= 0;
    }

    public function commit(): bool
    {
        if (!--$this->transactions) {
            return $this->getPdo()->commit();
        }
        return $this->transactions >= 0;
    }

    public function rollback(): bool
    {
        if (--$this->transactions) {
            $this->execute('ROLLBACK TO trans' . $this->transactions + 1);
            return true;
        }
        return $this->getPdo()->rollback();
    }

    /**
     * substitute arrays for prepared statements items
     */
    public function prepareQuery(string &$query, array|object|null &$params = null): void
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
	 * returns number of affected rows or true if succeeded, false if failed
     */
    public function execute(string $query, array|object|null $params = null): int|bool
    {
        try {
            $this->prepareQuery($query, $params);
            $this->lastQuery = $query;

            $stm = $this->getPdo()->prepare($query);
            $stm->execute($params);
            $this->lastStatement = $stm;

            if (!empty($this->getPdo()->lastInsertId())) {
                $this->lastId = intval($this->getPdo()->lastInsertId());
            }

            return $stm->rowCount();
        } catch (\Exception $e) {
            throw new DbException($e->getMessage(), $e->getCode(), null, $query);
        }
    }

    /**
     * Executes an SQL statement, returning a result set as a PDOStatement object
     *
	 * @template T of object
	 * @param class-string<T> $classname
	 * @return array<int,T>
     */
    public function query(string $query, array|object|null $params = null, string $classname = 'stdClass'): array
    {
        try {
            $this->prepareQuery($query, $params);
            $this->lastQuery = $query;

            /** @var DbStatement $stm */
            $stm = $this->getPdo()->prepare($query);
            $stm->execute($params);
            $this->lastStatement = $stm;

            $rows = [];
            while ($row = $stm->fetchMappedObject($classname)) {
                /** @var T $row */
                $rows[] = $row;
            }
            return $rows;
        } catch (\Exception $e) {
            throw new DbException($e->getMessage(), $e->getCode(), $e, $query);
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
	public function queryOne(string $query, array|object|null $params = null, string $classname = 'stdClass'): ?object
	{
        try {
            $this->prepareQuery($query, $params);
            $this->lastQuery = $query;

            /** @var DbStatement $stm */
            $stm = $this->getPdo()->prepare($query);
            $stm->execute($params);
            $this->lastStatement = $stm;

            /** @var T|null $row */
            $row = $stm->fetchMappedObject($classname);

            return $row;
        } catch (\Exception $e) {
            throw new DbException($e->getMessage(), $e->getCode(), $e, $query);
        }
	}

	/**
	 * execute sql SELECT statement
	 * returns string representation of first value of the first row of the result
	 * or null if nothing selected
	 */
	public function queryVal(string $query, array|object|null $params = null): mixed
	{
        try {
            $this->prepareQuery($query, $params);
            $this->lastQuery = $query;

            $stm = $this->getPdo()->prepare($query);
            $stm->execute($params);
            $this->lastStatement = $stm;

            return $stm->fetchColumn(0);
        } catch (\Exception $e) {
            throw new DbException($e->getMessage(), $e->getCode(), $e, $query);
        }
	}

    /**
     * query a single value, always convert to int
     */
    public function queryInt(string $query, array|object|null $params = null): int
    {
        $v = $this->queryVal($query, $params);
        return intval($v);
    }

    /**
     * query a single value, always convert to string
     * returns empty string for null
     */
    public function queryString(string $query, array|object|null $params = null, string $nullvalue = ''): string
    {
        $v = $this->queryVal($query, $params);
        return strval($v ?? $nullvalue);
    }

    /**
     * query a single value, always convert to float
     */
    public function queryFloat(string $query, array|object|null $params = null): float
    {
        $v = $this->queryVal($query, $params);
        return floatval($v);
    }

    /**
     * query a single value, always convert to bool
     */
    public function queryBool(string $query, array|object|null $params = null): bool
    {
        $v = $this->queryVal($query, $params);
        return boolval($v);
    }

    /**
     * execute sql SELECT statement
     * returns lookup table as [row[key] => row[value], ...] for each fetched row
     * where $key and $value are column names retrieved by the query
     * if $key is empty the returned list is a plain array of [value, value, ...]
     *
     * @template T of object
     * @param class-string<T> $classname
     */
    public function queryList(string $sql, string $key, string $value, array|object|null $params = null, string $classname = 'stdClass'): array
    {
        $rows = $this->query($sql, $params, $classname);

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
     * returns array of [key => row, ...]
     * where key is a column returned in the query results
     *
     * @template T of object
     * @param class-string<T> $classname
     */
    public function queryAssoc(string $sql, string $key, array|object|null $params = null, string $classname = 'stdClass'): array
    {
        $rows = $this->query($sql, $params, $classname);
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
	public function insert(string $table, array|object $values): int|bool
	{
		if (is_object($values)) $values = get_object_vars($values);
		$cols = implode(', ', array_keys($values));
		$vals = preg_replace('/([^, ]+)/', ':$1', $cols);
		return $this->execute("INSERT INTO {$table} ({$cols}) VALUES ({$vals})", $values);
	}

	/**
	 * convenience function: insert a row ignoring errors
	 * $values is [column => value, ...] or an object
	 */
	public function insertIgnore(string $table, array|object $values): int|bool
	{
		if (is_object($values)) $values = get_object_vars($values);
		$cols = implode(', ', array_keys($values));
		$vals = preg_replace('/([^, ]+)/', ':$1', $cols);
		return $this->execute("INSERT IGNORE INTO {$table} ({$cols}) VALUES ({$vals})", $values);
	}

	/**
	 * convenience function: delete a row matching all conditions
	 * $values is [column => value, ...] or an object
	 * $values must contain at least one condition
	 */
	public function delete(string $table, array|object $values): int|bool
    {
		if (is_object($values)) $values = get_object_vars($values);
        $where = [];
		foreach (array_keys($values) as $col) {
			$where[] = "{$col} = :{$col}";
        }
		$where = implode(' AND ', $where);

        return $this->execute("DELETE FROM {$table} WHERE {$where}", $values);
    }

	/**
	 * convenience function: update a row
	 * $primaryKey is the name of the primary key column, must be in $values.
	 * $values is [column => value, ...] or an object
	 *
	 * note: cannot change primary key with this function
	 */
	public function update(string $table, string $primaryKey, array|object $values): int|bool
	{
		if (is_object($values)) $values = get_object_vars($values);

		$set = [];
		$vals = [];
		foreach ($values as $column => $value) {
			if ($column != $primaryKey) {
				$set[] = "{$column} = :{$column}";
			}
			$vals[$column] = $value;
		}

		$set = implode(', ', $set);
		$sql = "UPDATE {$table} SET {$set} WHERE {$primaryKey} = :{$primaryKey}";
		return $this->execute($sql, $vals);
	}

	/**
	 * insert (cols) values (vals) on duplicate key update col=val, ...
	 * $primaryKey is not updated
	 * values is [column => value, ...] or an object
	 */
	public function insertUpdate(string $table, string $primaryKey, array|object $values): int|bool
	{
		if (is_object($values)) $values = get_object_vars($values);
		if (empty($values[$primaryKey])) $values[$primaryKey] = null;
		$cols = implode(', ', array_keys($values));
		$vals = preg_replace('/([^, ]+)/', ':$1', $cols);

		$set = [];
		foreach (array_keys($values) as $column) {
			if ($column != $primaryKey) {
				$set[] = "{$column} = :{$column}";
			}
		}

		$set = implode(', ', $set);

		if ($set) {
			$sql = "INSERT INTO {$table} ({$cols}) VALUES ({$vals}) ON DUPLICATE KEY UPDATE {$set}";
		} else {
			// only got primary key column, insert ignore new row
			$sql = "INSERT IGNORE INTO {$table} ({$cols}) VALUES ({$vals})";
		}

		return$this->execute($sql, $values);
	}

    /**
     * Predict the next insert ID of the table
     * Taken From: http://dev.mysql.com/doc/refman/5.0/en/innodb-auto-increment-handling.html
     */
    public function getNextInsertId(string $table): int
    {
        $stm = $this->getPdo()->prepare("SELECT AUTO_INCREMENT FROM information_schema.tables WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table");
        $stm->execute(compact('table'));
        return intval($stm->fetchColumn());
    }

    /**
     * Return an array with [limit, offset, total] values for a query
     */
    public function countFoundRows(string $sql, ?array $params = null): array
    {
        if (!$sql) return [0, 0, 0];
        if (stripos($sql, 'select ') !== 0) return [0, 0, 0];

        $limit = 0;
        $offset = 0;
        $total = 0;
        $cSql = $sql;   // query without limit/offset
        if (preg_match('/(.*)?(LIMIT\s([0-9]+)((\s+OFFSET\s)?|(,\s?)?)([0-9]+)?)+$/is', trim($sql), $match)) {
            $cSql = trim($match[1] ?? '');
            $limit = (int)($match[3] ?? 0);
            $offset = (int)($match[7] ?? 0);
        }

        // No limit no need to continue
        if (!$limit) return [0, 0, 0];
        if ($limit == 1) return [0, 0, 1];

        $countSql = "SELECT COUNT(*) as i FROM ($cSql) as t";
        $stm = $this->getPdo()->prepare($countSql);
        if (false === $stm->execute($params)) {
            $info = $this->getPdo()->errorInfo();
            throw new Exception(end($info));
        }
        $stm->setFetchMode(\PDO::FETCH_ASSOC);
        $row = $stm->fetch();
        if ($row) $total = (int) $row['i'];
        return [$limit, $offset, $total];
    }

    public function getTableInfo(string $table): array
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

        $query = "DESCRIBE `{$table}`";
        try {
            $list = [];
            $stm = $this->getPdo()->prepare($query);
            $stm->execute();
            $stm->setFetchMode(\PDO::FETCH_ASSOC);

            foreach ($stm as $row) {
                $col = (object)$row;
                $col->name = $col->Field;
                $col->name_camel = lcfirst(str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $col->name))));
                preg_match('/^([a-z0-9_]+)(\(([0-9]+)\))?(.+)?/i', $col->Type, $r);

                $col->mysql_type = $r[1] ?? 'unknown';
                $col->len = intval($r[3] ?? 0);
                $col->ext = trim($r[4] ?? '');
                $col->php_type = $types[$col->mysql_type] ?? 'unknown';
                if ($col->php_type == 'int' && $col->len == 1) $col->php_type = 'bool';
                if (str_starts_with($col->name, 'json_')) $col->php_type = 'json';

                $col->is_primary_key = $col->Key == 'PRI';
                $col->is_unique      = in_array($col->Key, ['PRI', 'UNI']);
                $col->is_numeric     = in_array($col->php_type, ['int', 'tinyint', 'decimal', 'float']);
                $col->is_string      = in_array($col->php_type, ['string', 'timestamp', 'datetime', 'date', 'time', 'year']);
                $col->is_datetime    = in_array($col->php_type, ['timestamp', 'datetime', 'date', 'time', 'year']);
                $col->is_enum        = ($col->mysql_type == 'enum');
                $col->is_set         = ($col->mysql_type == 'set');

                $list[$col->name] = $col;
            }
            return $list;
        } catch (\Exception $e) {
            throw new DbException($e->getMessage(), $e->getCode(), null, $query);
        }
    }

    public function getDatabaseList(): array
    {
        $stm = $this->getPdo()->prepare("SHOW DATABASES");
        $stm->execute();
        return $stm->fetchAll(\PDO::FETCH_COLUMN, 0);
    }

    public function getTableList(): array
    {
        $stm = $this->getPdo()->prepare("SHOW TABLES");
        $stm->execute();
        return $stm->fetchAll(\PDO::FETCH_COLUMN, 0);
    }

    public function tableExists(string $table): bool
    {
        $stm = $this->getPdo()->prepare("SHOW TABLES LIKE :table");
        $stm->execute(compact('table'));
        return $stm->fetchColumn() !== false;
    }

    public function dropTable(string $tableName): int
    {
        $tableName = self::escapeTable($tableName);
        if (!$this->tableExists($tableName)) return 0;
        $query = "SET FOREIGN_KEY_CHECKS = 0;SET UNIQUE_CHECKS = 0;\n";
        $query .= "DROP TABLE IF EXISTS `{$tableName}` CASCADE;\n";
        $query .= "SET FOREIGN_KEY_CHECKS = 1;SET UNIQUE_CHECKS = 1;";
        $stm = $this->getPdo()->prepare($query);
        $stm->execute();
        return $stm->rowCount();
    }

    /**
     * Remove all tables from a DB
     * You must send true as a parameter to ensure it executes
     */
    public function dropAllTables(bool $confirm = false, array $exclude = []): int
    {
        if (!$confirm) return 0;
        $query = "SET FOREIGN_KEY_CHECKS = 0;SET UNIQUE_CHECKS = 0;\n";
        foreach ($this->getTableList() as $v) {
            if (in_array($v, $exclude)) continue;
            $query .= "DROP TABLE IF EXISTS `{$v}` CASCADE;\n";
        }
        $query .= "SET FOREIGN_KEY_CHECKS = 1;SET UNIQUE_CHECKS = 1;";
        $stm = $this->getPdo()->prepare($query);
        $stm->execute();

        return $stm->rowCount();
    }

    /**
     * sanitize a table/Db name for queries
     */
    public static function escapeTable($table): string
    {
        return preg_replace('/[^a-z0-9_]/i', '', $table);
    }
}
