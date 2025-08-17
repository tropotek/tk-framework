<?php

namespace Tk\Db;

use Tk\Date;
use Tk\Db;

class MySqlSession implements \SessionHandlerInterface
{
    public static int    $DATA_TTL_MINS = 60*24;
    public static string $DB_TABLE      = '_session';


    public function __construct()
    {
        $this->installTable();
        $this->clearExpired();
    }

    /**
     * return true if the table exists or is installed
     */
    public function installTable(): bool
    {
        if (Db::tableExists(self::$DB_TABLE)) return true;
        return false !== Db::execute($this->getTableSql());
    }

    protected function getTableSql(): string
    {
        $table = self::$DB_TABLE;
        return <<<SQL
            CREATE TABLE IF NOT EXISTS {$table} (
                session_id VARCHAR(128) NOT NULL PRIMARY KEY,
                data BLOB NOT NULL,
                expiry TIMESTAMP NOT NULL DEFAULT (CURRENT_TIMESTAMP + INTERVAL 30 MINUTE),
                modified TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            )
        SQL;
    }

    public function clearExpired(): bool
    {
        $table = self::$DB_TABLE;
        return false !== Db::execute("DELETE FROM $table WHERE expiry < NOW()");
    }

    /**
     * Connect to your database
     * Ensure error handling is in place
     */
    public function open(string $path, string $name): bool
    {
        $this->installTable();
        return true;
    }

    /**
     * Close the database connection if necessary
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * Read session data from the database
     */
    public function read(string $id): string|false
    {
        $table = self::$DB_TABLE;
        $value = Db::queryVal("
            SELECT data
            FROM $table
            WHERE session_id = :id",
            compact('id')
        );
        if ($value === false) return '';
        return $value;
    }

    /**
     * Write or update session data in the database
     */
    public function write(string $id, string $data): bool
    {
        // Create time stamp
        $table = self::$DB_TABLE;
        $time = new \DateTimeImmutable();
        $expiry = $time->modify("+" . self::$DATA_TTL_MINS . " minutes")->format(Date::FORMAT_ISO_DATETIME);
        return (false !== Db::execute("
            INSERT INTO $table (session_id, data, expiry)
            VALUES (:id, :data, :expiry)
            ON DUPLICATE KEY UPDATE expiry = :expiry, data = :data",
            compact('id', 'data', 'expiry')
        ));
    }

    /**
     * Delete session from the database
     */
    public function destroy(string $id): bool
    {
        return (false !== Db::delete(self::$DB_TABLE, ['session_id' => $id]));
    }

    /**
     * Garbage collection: delete expired sessions
     */
    public function gc(int $max_lifetime): int|false
    {
        $table = self::$DB_TABLE;
        $n = Db::execute("
            DELETE FROM $table WHERE UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(modified) > :max_lifetime",
            compact('max_lifetime')
        );
        if (is_int($n)) return $n;
        return false;
    }
}