<?php
namespace Tk\Auth\Storage;


class SessionStorage implements StorageInterface
{
    /**
     * user id namespace (username)
     */
    public static string $SID_USER = '_auth.user';


    public function isEmpty(): bool
    {
        return !isset($_SESSION[self::$SID_USER]);
    }

    public function read(): mixed
    {
        return $_SESSION[self::$SID_USER] ?? '';
    }

    public function write(mixed $contents): void
    {
        $_SESSION[self::$SID_USER] = $contents;
    }

    public function clear(): void
    {
        unset($_SESSION[self::$SID_USER]);
    }

}
