<?php
namespace Tk\Db;

use Tt\Db;

/**
 * A basic DB session handler
 *
 */
class Session
{
    public static int $DATA_TTL_DAYS = 15;


    public function __construct()
    {
        // Set handler to override SESSION
        session_set_save_handler(
            [$this, "_open"],
            [$this, "_close"],
            [$this, "_read"],
            [$this, "_write"],
            [$this, "_destroy"],
            [$this, "_gc"]
        );

        session_start();
    }

    public function __destruct()
    {
        session_write_close();
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


    public function _open(): bool
    {
        return true;
    }

    public function _close(): bool
    {
        return true;
    }

    public function _read(string $session_id): string
    {
        return Db::queryVal("
            SELECT data
            FROM _session
            WHERE session_id = :session_id",
            compact('session_id')
        );
    }

    public function _write(string $session_id, mixed $data): bool
    {
        // Create time stamp
        $time = new \DateTimeImmutable();
        $lifetime = $time->modify("+" . self::$DATA_TTL_DAYS . " days")->getTimestamp();
        $time = $time->getTimestamp();

        return (false !== Db::execute("
            REPLACE INTO _session VALUES (:session_id, :data, :lifetime, :time)",
            compact('session_id', 'data', 'lifetime', 'time')
        ));
    }

    public function _destroy(string $session_id): bool
    {
        return (false !== Db::delete('_session', compact('session_id')));
    }

    /**
     * Garbage Collection
     */
    public function _gc($max) {
        // Calculate what is to be deemed old
        $old = time() - $max;
        return (false !== Db::execute("
            DELETE FROM _session WHERE time < :old",
            compact('old')
        ));
    }

}


