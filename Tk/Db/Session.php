<?php
namespace Tk\Db;

use Tk\Traits\SingletonTrait;
use Tk\Db;

/**
 * A basic DB session handler
 *
 * @todo allow for db/session params to be sent as options in the constructor
 */
class Session implements \SessionHandlerInterface
{
    use SingletonTrait;

    public static int $DATA_TTL_DAYS = 15;


    public function __construct(array $options = [])
    {
        // Set handler to override SESSION
        session_set_save_handler($this);
    }

    public function __destruct()
    {
        session_write_close();
    }

    public static function instance(array $options = []): static
    {
        if (self::$_INSTANCE == null) {
            self::$_INSTANCE = new static($options);
        }
        return self::$_INSTANCE;
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
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string
    {
        return Db::queryVal("
            SELECT data
            FROM _session
            WHERE session_id = :id",
            compact('id')
        );
    }

    public function write(string $id, mixed $data): bool
    {
        // Create time stamp
        $time = new \DateTimeImmutable();
        $lifetime = $time->modify("+" . self::$DATA_TTL_DAYS . " days")->getTimestamp();
        $time = $time->getTimestamp();

        return (false !== Db::execute("
            REPLACE INTO _session VALUES (:id, :data, :lifetime, :time)",
            compact('id', 'data', 'lifetime', 'time')
        ));
    }

    public function destroy(string $id): bool
    {
        return (false !== Db::delete('_session', ['session_id' => $id]));
    }

    /**
     * Rubbish Collection
     */
    public function gc(int $max_lifetime): false|int
    {
        $old = time() - $max_lifetime;
        return Db::execute("
            DELETE FROM _session WHERE time < :old",
            compact('old')
        );
    }

}


