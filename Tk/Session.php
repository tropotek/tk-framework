<?php

namespace Tk;


class Session
{
    const string SID_IP           = '_user.ip';
    const string SID_AGENT        = '_user.agent';
    const string SID_PAGE_REFERER = '_user.pagereferer';

    protected static mixed $_instance = null;


    public function __construct(?\SessionHandlerInterface $handler = null)
    {
        if (!is_null($handler)) {
            session_set_save_handler($handler, true);
        }
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.use_strict_mode', '1');
            $secure = (($_SERVER['HTTPS'] ?? '') !== '' && $_SERVER['HTTPS'] !== 'off')
                || (($_SERVER['SERVER_PORT'] ?? '') == 443)
                || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'secure'   => $secure,
                'httponly' => true,
                'samesite' => self::getCookieSameSite(),
            ]);
            session_start();
        }

        $_SESSION[self::SID_IP]      = System::getClientIp();
        $_SESSION[self::SID_AGENT]   = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (!isset($_SESSION[self::SID_PAGE_REFERER]) && isset($_SERVER['HTTP_REFERER'])) {
            $_SESSION[self::SID_PAGE_REFERER] = $_SERVER['HTTP_REFERER'] ?? '';
        }
    }

    /**
     * Resolve the session cookie SameSite policy.
     *
     * Defaults to Lax: 'Strict' withholds the cookie on cross-site top-level
     * navigations, which breaks any external auth callback redirecting back
     * into the site (oAuth/SSI). Only set 'Strict' on sites with no such flow.
     */
    protected static function getCookieSameSite(): string
    {
        $samesite = ucfirst(strtolower(trim(strval(
            Config::getValue('session.cookie.samesite', Cookie::SAMESITE_LAX)
        ))));

        $valid = [Cookie::SAMESITE_NONE, Cookie::SAMESITE_LAX, Cookie::SAMESITE_STRICT];
        if (!in_array($samesite, $valid, true)) {
            return Cookie::SAMESITE_LAX;
        }
        return $samesite;
    }

    public static function instance(?\SessionHandlerInterface $handler = null): self
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($handler);
        }
        return self::$_instance;
    }

    /**
     * Regenerate the session id, preserving session data.
     * Call on every privilege elevation (login) to prevent session fixation.
     */
    public function regenerateId(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    // session cache methods with timeout

    /**
     * save a value to the session cache with optional timeout (seconds)
     * timeout = 0 means use default session expiry timeout
     */
    public static function set(string $name, mixed $data, int $timeout_seconds = 60): void
    {
        self::expire();
        if (!isset($_SESSION['cache'])) $_SESSION['cache'] = [];
        $_SESSION['cache'][$name] = [
            'timeout'      => ($timeout_seconds > 0) ? (time() + $timeout_seconds) : 0,
            'data'         => $data,
        ];
    }

    /**
     * get a value from the session cache
     */
    public static function get(string $name, mixed $default = null): mixed
    {
        self::expire();
        return $_SESSION['cache'][$name]['data'] ?? $default;
    }

    /**
     * check if a session cache value exists
     */
    public static function has(string $name): bool
    {
        self::expire();
        return array_key_exists($name, $_SESSION['cache'] ?? []);
    }

    /**
     * get a value from the session cache and remove it
     */
    public static function once(string $name): mixed
    {
        $val = self::get($name);
        self::remove($name);
        return $val;
    }

    /**
     * removes a session cache value
     */
    public static function remove(string $name): void
    {
        self::expire();
        unset($_SESSION['cache'][$name]);
    }

    /**
     * clear expired values from the session cache
     */
    public static function expire(): void
    {
        foreach (array_keys($_SESSION['cache'] ?? []) as $name) {
            $timeout = $_SESSION['cache'][$name]['timeout'] ?? 0;
            if ($timeout && $timeout < time()) {
                unset($_SESSION['cache'][$name]);
            }
        }
    }

}