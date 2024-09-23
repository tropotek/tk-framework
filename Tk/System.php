<?php
namespace Tk;

/**
 * The System object will contain all system information methods
 * and any methods to set the system state.
 */
class System
{

    /**
     * Return the root path to the site.
     */
    public static function discoverBasePath(): string
    {
        return rtrim(dirname(__DIR__, 4), DIRECTORY_SEPARATOR);
    }

    /**
     * Attempt to locate .htaccess and find a RewriteBase parameter to use
     */
    public static function discoverBaseUrl(): string
    {
        $path = '/';
        $htaccessFile = self::discoverBasePath() . '/.htaccess';
        if (is_file($htaccessFile)) {
            $htaccess = file_get_contents($htaccessFile);
            if ($htaccess && preg_match('/\s+RewriteBase (\/.*)\s+/i', $htaccess, $regs)) {
                $path = $regs[1] ?? '';
            }
        }
        return rtrim($path, '/');
    }

    /**
     * Returns the client IP address.
     *
     * This method can read the client IP address from the "X-Forwarded-For" header
     * when trusted proxies were set via "setTrustedProxies()". The "X-Forwarded-For"
     * header value is a comma+space separated list of IP addresses, the left-most
     * being the original client, and each successive proxy that passed the request
     * adding the IP address where it received the request from.
     */
    public static function getClientIp(): string
    {
        $ip = $_SERVER['HTTP_CLIENT_IP'] ?? ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ($_SERVER['REMOTE_ADDR'] ?? ''));

        if (substr_count($ip, ':') > 1) {   // is ip 6
            if (!filter_var($ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6)) $ip = '';
        } else {
            if (!filter_var($ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4)) $ip = '';
        }
        return $ip;
    }

    /**
     * Check if the user requested a cache refresh using <Ctrl>+<Shift>+R
     */
    public static function isRefreshCacheRequest(): bool
    {
        return (
            ($_SERVER['HTTP_PRAGMA'] ?? '') == 'no-cache' ||
            ($_SERVER['HTTP_CACHE_CONTROL'] ?? false) == 'no-cache'
        );
    }

    /**
     * Get the current script running time in seconds
     */
    public static function scriptDuration(): string
    {
        return (string)(microtime(true) - Config::instance()->get('script.start.time'));
    }

    /**
     * Test if the request is run from a Command Line Interface (CLI)
     */
    public static function isCli(): bool
    {
        return (str_starts_with(php_sapi_name(), 'cli'));
    }

    /**
     * Get the composer.json as an array
     */
    public static function getComposerJson(): array
    {
        static $composer = null;
        if (!$composer) {
            $composer = [];
            if (is_file(Config::makePath('/composer.json'))) {
                $composer = json_decode(file_get_contents(Config::makePath('/composer.json')), true);
            }
        }
        return $composer;
    }

    /**
     * Get the version found in the version file (if any)
     * Returns "1.0" if no version file found
     */
    public static function getVersion(): string
    {
        static $version = null;
        if (!$version) {
            $version = '1.0.0';
            if (is_file(Config::makePath('/version'))) {
                $version = file_get_contents(Config::makePath('/version'));
            } else if (is_file(Config::makePath('/version.md'))) {
                $version = file_get_contents(Config::makePath('/version.md'));
            }
        }
        return $version;
    }

}