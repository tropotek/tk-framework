<?php
namespace Tk;

use Tk\Cache\Cache;

/**
 * The System object will contain all system information methods
 * and any methods to set the system state.
 */
class System
{

    /**
     * @return string
     */
    public static function discoverHostname(): string
    {
        $key = 'hostname';
        $cache = new Cache();
        if (empty($hostname = $cache->fetch($key)) || System::isRefreshCacheRequest()) {
            $hostname = $_SERVER['HTTP_HOST'] ?? $_SERVER['HTTP_X_FORWARDED_HOST'] ?? 'localhost';
            if (!self::isCli()) {
                $cache->store($key, $hostname);
            }
        }
        return $hostname;
    }

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

        // check for an .htaccess mod_rewrite 'RewriteBase' path
        if (self::isCli() && empty($baseUrl)) {
            $htaccessFile = System::discoverBasePath() . '/.htaccess';
            if (is_readable($htaccessFile)) {
                $htaccess = file_get_contents($htaccessFile);
                if ($htaccess !== false && preg_match('/\s+RewriteBase (\/.*)\s+/i', $htaccess, $regs)) {
                    $baseUrl = $regs[1];
                }
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
     * By default this returns the direct TCP peer address ($_SERVER['REMOTE_ADDR'])
     * and ignores any "X-Forwarded-For"/"X-Client-IP" headers, since those are
     * trivially spoofable by any client when nothing sits in front of the app.
     *
     * If the immediate peer's address matches an entry in the
     * "system.trustedProxies" config value (an array of exact IPs and/or CIDR
     * blocks, e.g. ['10.0.0.0/8', '192.168.1.5'], default empty), the
     * "X-Forwarded-For" header (falling back to "X-Client-IP") is honoured
     * instead. That header is a comma-separated hop list, left-most being the
     * original client; this walks it from the right and returns the first hop
     * that is not itself a trusted proxy, so a chain of trusted proxies is
     * transparently skipped through to the real client.
     */
    public static function getClientIp(): string
    {
        $remoteAddr = self::validateIp($_SERVER['REMOTE_ADDR'] ?? '');

        $trustedProxies = Config::getValue('system.trustedProxies', []);
        if (empty($trustedProxies) || $remoteAddr === '' || !self::ipIsTrustedProxy($remoteAddr, $trustedProxies)) {
            return $remoteAddr;
        }

        $forwardedFor = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? ($_SERVER['HTTP_CLIENT_IP'] ?? '');
        $hops = array_values(array_filter(array_map(
            fn(string $hop) => self::validateIp($hop),
            explode(',', $forwardedFor)
        ), fn(string $ip) => $ip !== ''));

        for ($i = count($hops) - 1; $i >= 0; $i--) {
            if (!self::ipIsTrustedProxy($hops[$i], $trustedProxies)) {
                return $hops[$i];
            }
        }

        // Every forwarded hop was itself a trusted proxy (or the header was
        // missing/unparseable) - fall back to the immediate peer address.
        return $remoteAddr;
    }

    /**
     * Returns $ip if it is a well-formed IPv4/IPv6 address, empty string otherwise.
     */
    private static function validateIp(string $ip): string
    {
        $ip = trim($ip);
        if ($ip === '') return '';
        $flag = substr_count($ip, ':') > 1 ? \FILTER_FLAG_IPV6 : \FILTER_FLAG_IPV4;
        return filter_var($ip, \FILTER_VALIDATE_IP, $flag) ? $ip : '';
    }

    /**
     * @param string[] $trustedProxies Exact IPs and/or CIDR blocks (e.g. '10.0.0.0/8').
     */
    private static function ipIsTrustedProxy(string $ip, array $trustedProxies): bool
    {
        foreach ($trustedProxies as $proxy) {
            if (self::ipMatchesCidr($ip, (string)$proxy)) return true;
        }
        return false;
    }

    /**
     * Matches $ip against $cidr, which may be a bare IP (exact match) or an
     * IP/prefix-length CIDR block. Supports IPv4 and IPv6; a mismatched
     * address family never matches.
     */
    private static function ipMatchesCidr(string $ip, string $cidr): bool
    {
        if (!str_contains($cidr, '/')) {
            return $ip !== '' && $ip === $cidr;
        }

        [$subnet, $maskBits] = explode('/', $cidr, 2);
        if (!ctype_digit($maskBits)) return false;
        $maskBits = (int)$maskBits;

        $ipBin = @inet_pton($ip);
        $subnetBin = @inet_pton($subnet);
        if ($ipBin === false || $subnetBin === false || strlen($ipBin) !== strlen($subnetBin)) {
            return false;
        }

        $maxBits = strlen($ipBin) * 8;
        if ($maskBits < 0 || $maskBits > $maxBits) return false;

        $bytes = intdiv($maskBits, 8);
        $remainderBits = $maskBits % 8;

        if ($bytes > 0 && substr($ipBin, 0, $bytes) !== substr($subnetBin, 0, $bytes)) {
            return false;
        }
        if ($remainderBits === 0) return true;

        $mask = chr((0xFF << (8 - $remainderBits)) & 0xFF);
        return (substr($ipBin, $bytes, 1) & $mask) === (substr($subnetBin, $bytes, 1) & $mask);
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
        $start = $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true);
        return (string)(microtime(true) - $start);
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
            $json = file_get_contents(Path::create('/composer.json'));
            if ($json !== false) {
                $composer = json_decode($json, true);
            }
        }
        return $composer;
    }

    /**
     * Get the version found in the version file (if any)
     * Returns default if no version file found
     */
    public static function getVersion(string $default = '1.0.0'): string
    {
        static $version = null;
        if (!$version) {
            if (is_file(Path::create('/version'))) {
                $version = file_get_contents(Path::create('/version'));
            } else if (is_file(Path::create('/version.md'))) {
                $version = file_get_contents(Path::create('/version.md'));
            } else {
                return $default;
            }
        }
        return $version;
    }

    public static function getReleaseDate(): \DateTime
    {
        $released = new \DateTime();
        if (is_file(Path::create('/version'))) {
            $released = Date::create((int)filemtime(Path::create('/version')));
        } else if (is_file(Path::create('/version.md'))) {
            $released = Date::create((int)filemtime(Path::create('/version.md')));
        }
        return $released;
    }

}