<?php
namespace Tt;

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
		[$host, $user, $pass, $this->dbName] = explode('/', $dsn);
        $port = 3306;
        if (str_contains($host, ':')) {
            [$host, $port] = explode(':', $host);
        }

        $pdoDsn = sprintf('mysql:host=%s;port=%s;charset=utf8mb4;dbname=%s', $host, $port, $this->dbName);
        $this->pdo = new \PDO($pdoDsn, $user, $pass, $options);
        $this->pdo->setAttribute(\PDO::ATTR_STATEMENT_CLASS, [DbStatement::class, [$this]]);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);
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
     * substitute array values for IN queries
     */
    private function prepareQuery(string $query, array|object|null $params = null): array
    {
		if (is_object($params)) $params = get_object_vars($params);
        if (!is_array($params)) return [$query, $params];
        // is sequential
        if (array_keys($params) === range(0, count($params) - 1)) return [$query, $params];

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

        return [$query, $newParams];
    }

    /**
     * Execute an SQL statement that doesn't get a result set
	 * e.g. update, insert, delete
	 * returns number of affected rows or true if succeeded, false if failed
     */
    public function execute(string $query, array|object|null $params = null): int|bool
    {
        try {
            [$query, $params] = $this->prepareQuery($query, $params);
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
            [$query, $params] = $this->prepareQuery($query, $params);
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
            [$query, $params] = $this->prepareQuery($query, $params);
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
            [$query, $params] = $this->prepareQuery($query, $params);
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


    public function getTableInfo(string $table): array
    {
        $query = sprintf('DESCRIBE %s ', $this->quoteParameter($table));
        try {
            $list = [];
            $stm = $this->getPdo()->prepare($query);
            $stm->execute();
            $stm->setFetchMode(\PDO::FETCH_ASSOC);
            foreach ($stm as $row) {
                $list[$row['Field']] = $row;
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
        $list = $this->getTableList();
        return in_array($table, $list);
    }

    public function dropTable(string $tableName): int
    {
        if (!$this->tableExists($tableName)) return 0;
        $query = "SET FOREIGN_KEY_CHECKS = 0;SET UNIQUE_CHECKS = 0;";
        $query .= sprintf("DROP TABLE IF EXISTS %s CASCADE;", $this->quoteParameter($tableName));
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
        $query = 'SET FOREIGN_KEY_CHECKS = 0;SET UNIQUE_CHECKS = 0;';
        foreach ($this->getTableList() as $i => $v) {
            if (in_array($v, $exclude)) continue;
            $query .= sprintf('DROP TABLE IF EXISTS %s CASCADE;', $this->quoteParameter($v));
        }
        $query .= 'SET FOREIGN_KEY_CHECKS = 1;SET UNIQUE_CHECKS = 1;';
        $stm = $this->getPdo()->prepare($query);
        $stm->execute();

        return $stm->rowCount();
    }

    public function quote(string $str, int $type = \Pdo::PARAM_STR): string
    {
        return $this->getPdo()->quote($str, $type);
    }

    public function escapeString(string $str): string
    {
        if ($str) {
            return substr($this->quote($str), 1, -1);
        }
        return $str;
    }

    public function quoteParameter(string $param, $quote = '`'): string
    {
        return $quote . trim($param, $quote) . $quote;
    }


	public static function mysqlDateTime(\DateTime $dt = null): ?string
	{
        return empty($dt) ? null : $dt->format('Y-m-d H:i:s');
	}

    public static function mysqlDate(\DateTime $dt = null): ?string
	{
        return empty($dt) ? null : $dt->format('Y-m-d');
	}

	public static function mysqlTime(\DateTime $dt = null): ?string
	{
        return empty($dt) ? null : $dt->format('H:i:s');
	}

}
