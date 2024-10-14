<?php
namespace Tk\Logger;

/**
 * Store the current request's log to the session
 */
class SessionLog extends LoggerInterface
{
    public static string $SID       = '__sessionLog';
    public static int    $MAX_LINES = 100;


    public function log($level, $message, array $context = array()): void   /** @phpstan-ignore-line */
    {
        if (!$this->canLog($level)) return;
        if (session_status() !== \PHP_SESSION_ACTIVE) return;

        $log = $_SESSION[self::$SID] ?? [];
        if (count($log) >= self::$MAX_LINES) {
            $n = (self::$MAX_LINES - count($log))+1;
            $log = array_splice($log, $n);
        }

        $log[] = substr($this->format($level, $message, $context), 0, 200);
        $_SESSION[self::$SID] = $log;
    }

    public static function getLog(): array
    {
        return $_SESSION[self::$SID] ?? [];
    }

    public static function clearLog(): void
    {
        if (session_status() !== \PHP_SESSION_ACTIVE) return;
        $_SESSION[self::$SID] = [];
        unset($_SESSION[self::$SID]);
    }
}