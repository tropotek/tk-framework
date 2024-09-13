<?php
namespace Tk\Db;

use Tk\Date;
use Tk\System;
use Tk\Db;

/**
 * A basic DB session handler
 *
 * @todo allow for db/session params to be sent as options in the constructor
 */
class Session implements \SessionHandlerInterface
{
    const SID_IP    = '_user.ip';
    const SID_AGENT = '_user.agent';

    protected static mixed $_instance = null;

    public static int    $DATA_TTL_DAYS = 5;
    public static string $DB_TABLE      = '_session';


    public function __construct(array $options = [])
    {
        // Set handler to override SESSION
        session_set_save_handler($this);
        $this->installTable();
    }

    public function __destruct()
    {
        session_write_close();
    }

    public static function instance(array $options = []): static
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new static($options);
            self::$_instance->clearExpired();
        }
        return self::$_instance;
    }

    public function clearExpired(): bool
    {
        $table = self::$DB_TABLE;
        return false !== Db::execute("DELETE FROM $table WHERE expiry < NOW()");
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
            CREATE TABLE IF NOT EXISTS $table (
                session_id VARCHAR(128) NOT NULL PRIMARY KEY,
                data BLOB NOT NULL,
                expiry DATETIME NOT NULL,
                modified TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
        SQL;
    }

    /**
     * save a named value to session cache with optional timeout (seconds)
     * timeout = 0 means never expires
     */
    function set(string $name, mixed $data, int $timeout_seconds = 60): void
    {
        if (!isset($_SESSION['cache'])) $_SESSION['cache'] = [];
        $_SESSION['cache'][$name] = [
            'timeout'      => ($timeout_seconds > 0) ? (time() + $timeout_seconds) : 0,
            'data'         => $data,
        ];
    }

    /**
     * expires cache and returns a value from session cache
     */
    function get(string $name): mixed
    {
        $this->expire();
        return $_SESSION['cache'][$name]['data'] ?? null;
    }

    /**
     * get the value from the session then remove it
     */
    function once(string $name): mixed
    {
        $val = $this->get($name);
        $this->remove($name);
        return $val;
    }

    /**
     * removes a session cache value
     */
    function remove(string $name): void
    {
        unset($_SESSION['cache'][$name]);
    }

    /**
     * removes expires values from session cache
     */
    function expire(): void
    {
        foreach (array_keys($_SESSION['cache'] ?? []) as $name) {
            $timeout = $_SESSION['cache'][$name]['timeout'] ?? 0;
            if ($timeout && $timeout < time()) {
                unset($_SESSION['cache'][$name]);
            }
        }
    }

    public function open(string $path, string $name): bool
    {
        $this->installTable();
        $_SESSION[self::SID_IP]    = System::getClientIp();
        $_SESSION[self::SID_AGENT] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string
    {
        $table = self::$DB_TABLE;
        return Db::queryVal("
            SELECT data
            FROM $table
            WHERE session_id = :id",
            compact('id')
        );
    }

    public function write(string $id, mixed $data): bool
    {
        // Create time stamp
        $table = self::$DB_TABLE;
        $time = new \DateTimeImmutable();
        $expiry = $time->modify("+" . self::$DATA_TTL_DAYS . " days")->format(Date::FORMAT_ISO_DATETIME);
        return (false !== Db::execute("
            INSERT INTO $table (session_id, data, expiry)
            VALUES (:id, :data, :expiry)
            ON DUPLICATE KEY UPDATE expiry = :expiry, data = :data",
            compact('id', 'data', 'expiry')
        ));
    }

    public function destroy(string $id): bool
    {
        return (false !== Db::delete(self::$DB_TABLE, ['session_id' => $id]));
    }

    /**
     * Sessions that have not updated for the last max_lifetime seconds will be removed.
     */
    public function gc(int $max_lifetime): false|int
    {
        $table = self::$DB_TABLE;
        return Db::execute("
            DELETE FROM $table WHERE UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(modified) > :max_lifetime",
            compact('max_lifetime')
        );
    }

}
