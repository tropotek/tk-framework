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
     * Discover the site's base URL.
     */
    public static function discoverBaseUrl(): string
    {
        $filename = basename($_SERVER['SCRIPT_FILENAME'] ?? '');

        if (basename($_SERVER['SCRIPT_NAME'] ?? '') === $filename) {
            $baseUrl = $_SERVER['SCRIPT_NAME'];
        } elseif (basename($_SERVER['PHP_SELF'] ?? '') === $filename) {
            $baseUrl = $_SERVER['PHP_SELF'];
        } else {
            // Backtrack up the script_filename to find the portion matching
            // php_self
            $path = $_SERVER['PHP_SELF'] ?? '';
            $file = $_SERVER['SCRIPT_FILENAME'] ?? '';
            $segs = explode('/', trim($file, '/'));
            $segs = array_reverse($segs);
            $index = 0;
            $last = \count($segs);
            $baseUrl = '';
            do {
                $seg = $segs[$index];
                $baseUrl = '/'.$seg.$baseUrl;
                ++$index;
            } while ($last > $index && (false !== $pos = strpos($path, $baseUrl)) && 0 != $pos);
        }

        // Does the baseUrl have anything in common with the request_uri?
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        if ('' !== $requestUri && '/' !== $requestUri[0]) {
            $requestUri = '/'.$requestUri;
        }

        if ($baseUrl && null !== $prefix = self::getUrlencodedPrefix($requestUri, $baseUrl)) {
            // full $baseUrl matches
            $baseUrl = $prefix;
        } else if ($baseUrl && null !== $prefix = self::getUrlencodedPrefix($requestUri, rtrim(\dirname($baseUrl), '/'.\DIRECTORY_SEPARATOR).'/')) {
            // directory portion of $baseUrl matches
            $baseUrl = rtrim($prefix, '/'.\DIRECTORY_SEPARATOR);
        } else {
            $truncatedRequestUri = $requestUri;
            if (false !== $pos = strpos($requestUri, '?')) {
                $truncatedRequestUri = substr($requestUri, 0, $pos);
            }
            $basename = basename($baseUrl ?? '');
            if (empty($basename) || !strpos(rawurldecode($truncatedRequestUri), $basename)) {
                // no match whatsoever; set it blank
                $baseUrl = '';
            }

            // If using mod_rewrite or ISAPI_Rewrite strip the script filename
            // out of baseUrl. $pos !== 0 makes sure it is not matching a value
            // from PATH_INFO or QUERY_STRING
            if (\strlen($requestUri) >= \strlen($baseUrl) && (false !== $pos = strpos($requestUri, $baseUrl)) && 0 !== $pos) {
                $baseUrl = substr($requestUri, 0, $pos + \strlen($baseUrl));
            }
        }

        return rtrim($baseUrl, '/'.\DIRECTORY_SEPARATOR);
    }


    /**
     * Returns the prefix as encoded in the string when the string starts with
     * the given prefix, null otherwise.
     */
    private static function getUrlencodedPrefix(string $string, string $prefix): ?string
    {
        if (!str_starts_with(rawurldecode($string), $prefix)) return null;
        $len = strlen($prefix);
        if (preg_match(sprintf('#^(%%[[:xdigit:]]{2}|.){%d}#', $len), $string, $match)) {
            return $match[0];
        }
        return null;
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
     * Is this request a HTMX request
     * @see https://htmx.org/docs/#request-headers
     */
    public static function isHtmx(): bool
    {
        return $_SERVER['HTTP_HX_REQUEST'] ?? false;
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