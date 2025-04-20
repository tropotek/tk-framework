<?php
namespace Tk;

use Psr\Http\Message\UriInterface;

/**
 *
 * <code>
 *   echo Uri::create('/full/uri/path/index.html')->toString();
 *   // Result:
 *   //  http://localhost/full/uri/path/index.html
 * </code>
 *
 * If the static $BASE_PATH is set this will be prepended to all relative paths
 * when creating a URI `Uri::create('/home.html')->toString()` => '/site/base/path/home.html'
 */
class Uri implements UriInterface
{

    /**
     * The sites base path from the web root, for relative URIs
     * Set this in your apps bootstrap file
     */
    public static string $BASE_PATH = '';

    /**
     * The sites hostname for relative URIs
     */
    public static string $SITE_HOST = 'localhost';

    /**
     * Per RFC 3986(Scheme): scheme = ALPHA *( ALPHA / DIGIT / "+" / "-" / "." )
     */
    const string SCHEME_REGEX_PATTERN = '/^(?:[a-z]+)(?:(?:[\+\.\-]*)(?:[a-z0-9]*))$/';

    /**
     * Per RFC 3986(Host): host = reg-name
     */
    const string HOST_REGEX_PATTERN = '(?:[\d\w\-\_]+)(?:(?:(?:\.)[\d\w\-\_]+)*)(?:(?:(?:\.)[a-z]+)*)';

    /**
     * Per RFC 3986(Host): host = IPv4address
     */
    const string IPV4_REGEX_PATTERN = '(?:\d{1,3})\.(?:\d{1,3})\.(?:\d{1,3})\.(?:\d{1,3})';

    /**
     * Per RFC 3986(Port): port = *DIGIT
     */
    const string PORT_REGEX_PATTERN = '/^(?:\d+)$/';

    private array $matched = [
        'http' => 80,
        'https' => 443,
        'ftp' => 21,
        'telnet' => 23,
        'ssh' => 22,
        'smtp' => 25,
    ];

    /**
     * The original uri string
     */
    protected string $spec   = '';

    private string $scheme   = '';
    private string $host     = '';
    private ?int   $port     = null;
    private string $user     = '';
    private string $password = '';
    private string $path     = '';
    private array  $query    = [];
    private string $fragment = '';


    public function __construct(?string $uri = null, array $queryParams = [])
    {
        // Build a request URI
        if (is_null($uri)) {
            $uri = '/';
            if (isset($_SERVER['REQUEST_URI'])) {
                $uri = $_SERVER['REQUEST_URI'];
                if (!empty($_SERVER['QUERY_STRING']) && !str_contains($uri, '?')) {
                    $uri .= '?' . $_SERVER['QUERY_STRING'];
                }
            }
        }

        // Prepend site base path if this is a relative Uri path only
        if (
            //!empty(trim(self::$BASE_PATH, '/')) &&
            str_starts_with($uri, '/') &&       // spec starts with a path
            !str_starts_with($uri, '//')        // ignore urls without scheme
        ) {
            if (str_starts_with($uri, self::$BASE_PATH)) {
                $uri = substr($uri, strlen(self::$BASE_PATH));
            }
            $uri = ($_SERVER['REQUEST_SCHEME'] ?? 'https') . '://' .
                self::$SITE_HOST .
                self::$BASE_PATH . '/' .
                trim($uri, '/');
        }

        // finalize the Uri
        $this->spec = $uri;
        $this->init($uri);
        $this->set($queryParams);
    }

    public function __serialize()
    {
        return ['spec' => $this->spec];
    }

    public function __unserialize(array $data)
    {
        $this->init($data['spec']);
    }

    public static function create(string|Uri|null $uri = null, array $queryParams = []): self
    {
        if ($uri instanceof Uri) return clone $uri;
        return new self($uri, $queryParams);
    }

    protected function init(string $uri): void
    {
        if (empty($uri) || self::isDataScheme($uri)) return;

        $parsed = parse_url($uri);
        $this->scheme = $parsed['scheme'] ?? '';
        $this->host = $parsed['host'] ?? '';
        $this->port = isset($parsed['port']) ? (int)$parsed['port'] : null;
        $this->user = $parsed['user'] ?? '';
        $this->password = $parsed['pass'] ?? '';
        $this->path = $parsed['path'] ?? '';
        $this->fragment = $parsed['fragment'] ?? '';
        parse_str(html_entity_decode($parsed['query'] ?? ''), $this->query);
    }

    /**
     * clear and reset the query string items
     */
    public function reset(): self
    {
        $this->query = [];
        return $this;
    }

    /**
     * Add a field to the query string
     */
    public function set(string|array $field, null|string|int|float|bool $value = null): self
    {
        if (is_array($field)) {
            foreach ($field as $k => $v) {
                if ($v === null) {
                    $field[$k] = $k;
                } elseif (is_bool($v)) {
                    $field[$k] = $v ? 'y' : 'n';
                } else {
                    $field[$k] = strval($v);
                }
            }
            $this->query = array_merge($this->query, $field);
            return $this;
        }
        if ($value === null) $value = $field;
        if (is_bool($value)) $value = $value ? 'y' : 'n';
        $this->query[$field] = strval($value);
        return $this;
    }

    /**
     * Get a value from the query string.
     */
    public function get(string $field): string
    {
        return $this->query[$field] ?? '';
    }

    /**
     * Get all the query params
     */
    public function all(): array
    {
        return $this->query;
    }

    /**
     * Check if a query field exists in the array
     */
    public function has(string $field): bool
    {
        return isset($this->query[$field]);
    }

    /**
     * Remove a field in the query string
     */
    public function remove(string $field): self
    {
        if ($this->has($field)) {
            unset($this->query[$field]);
        }
        return $this;
    }

    /**
     * returns true if the uri no-link|script|tel|mailto|data type URI and not a link URL
     */
    public static function isDataScheme(string $spec): bool
    {
        if ($spec === '#') return true;
        return (bool)preg_match('/^(javascript|script|mailto|tel|data):/', strtolower($spec));
    }

    public function getScheme(): string
    {
        return strtolower($this->scheme);
    }

    public function getAuthority(): string
    {
        $authority = '';
        $userInfo = $this->getUserInfo();
        $host = $this->getHost();
        $port = $this->getPort();

        $userInfo .= empty($userInfo) || empty($host) ? '' : '@';
        $authority .= empty($host) ? '' : $userInfo . $host;
        $authority .= !is_null($port) ? ':' . $port : '';

        return $authority;
    }

    public function getUserInfo(): string
    {
        $userInfo = $this->user;
        $userInfo .= empty($this->password) ? '' : ':' . $this->password;
        return $userInfo;
    }

    public function getHost(): string
    {
        return strtolower($this->host);
    }

    public function getPort(): ?int
    {
        if (empty($this->scheme) && is_null($this->port)) return null;
        if (($this->matched[$this->scheme] ?? null) === $this->port) {
            return null;
        }
        return $this->port;
    }

    public function getPath(): string
    {
        return empty($this->path) ? '/' : $this->path;
    }

    /**
     * If the $BASE_PATH is set the path is returned with the $BASE_PATH removed.
     */
    public function getRelativePath(): string
    {
        $path = $this->getPath();
        $path = urldecode($path);
        if (preg_match('/^'.  preg_quote(self::$BASE_PATH, '/') . '/', $path)) {
            $path = preg_replace('/^'.preg_quote(self::$BASE_PATH, '/').'/', '', $path);
        }
        return $path;
    }

    public function getQuery(): string
    {
        $query = '';
        foreach ($this->query as $field => $value) {
            if (is_array($value)) {
                foreach ($value as $v) {
                    $query .= urlencode($field) . '[]=' . urlencode($v) . '&';
                }
            } else {
                $query .= urlencode($field) . '=' . urlencode($value) . '&';
            }
        }
        return substr($query, 0, -1);
    }

    public function getFragment(): string
    {
        return $this->fragment;
    }

    public function __toString()
    {
        if ($this->isDataScheme($this->spec)) return $this->spec;

        $fullUri = '';
        $scheme = $this->getScheme();
        $fullUri .= empty($scheme) ? '' : $this->scheme . ':';

        $authority = $this->getAuthority();
        $fullUri .= empty($authority) ? '' : '//' . $authority;
        $path = $this->getPath();
        $fullUri .= empty($path) ? '' : '/' . ltrim($path, '/');
        $query = $this->getQuery();
        $fullUri .= empty($query) ? '' : '?' . $query;
        $fragment = $this->getFragment();
        $fullUri .= empty($fragment) ? '' : '#' . $fragment;

        return $fullUri;
    }

    public function toRelativeString(): string
    {
        if ($this->isDataScheme($this->spec)) return $this->spec;

        $fullUri = '';
        $path = $this->getRelativePath();
        $fullUri .= empty($path) ? '' : '/' . ltrim($path, '/');
        $query = $this->getQuery();
        $fullUri .= empty($query) ? '' : '?' . $query;
        $fragment = $this->getFragment();
        $fullUri .= empty($fragment) ? '' : '#' . $fragment;

        return $fullUri;
    }

    public function toString(): string
    {
        return $this->__toString();
    }

    public function withScheme(string $scheme): self
    {
        if (!$this->validateScheme($scheme)) {
            throw new \InvalidArgumentException(sprintf("Parameter 1 of %s require a valid URI scheme.", __METHOD__));
        }

        $q = clone $this;
        $q->scheme = $scheme;

        return $q;
    }

    public function withUserInfo(string $user, ?string $password = null): self
    {
        $q = clone $this;
        $q->user = $user;
        $q->password = is_null($password) ? '' : $password;

        return $q;
    }

    public function withHost(string $host): self
    {
        if (!$this->validateHost($host)) {
            throw new \InvalidArgumentException(sprintf("Parameter 1 of %s require a valid URI host.", __METHOD__));
        }

        $q = clone $this;
        $q->host = $host;

        return $q;
    }

    public function withPort(?int $port = null): self
    {
        if (!$this->validatePort($port)) {
            throw new \InvalidArgumentException(sprintf("Parameter 1 of %s requires a valid URI port.", __METHOD__));
        }

        $q = clone $this;
        $q->port = $port;

        return $q;
    }

    public function withPath(string $path): self
    {
        if (!$this->validatePath($path)) {
            throw new \InvalidArgumentException(sprintf("Parameter 1 of %s require a valid URI path.", __METHOD__));
        }

        $q = clone $this;
        $q->path = $path;

        return $q;
    }

    public function withQuery(string $query): self
    {
        if (empty($query)) {
            throw new \InvalidArgumentException(sprintf("Parameter 1 of %s require a valid URI query string.", __METHOD__));
        }

        $q = clone $this;
        parse_str(html_entity_decode($query), $q->query);

        return $q;
    }

    public function withFragment(string $fragment): self
    {
        $q = clone $this;
        $q->fragment = $fragment;

        return $q;
    }

    protected function validateScheme(string $scheme): bool
    {
        return !(empty($scheme) || !preg_match(self::SCHEME_REGEX_PATTERN, $scheme));
    }

    protected function validateHost(string $host): bool
    {
        $full = '/(?(?='
            . self::IPV4_REGEX_PATTERN
            . ')'
            . self::IPV4_REGEX_PATTERN
            . '|'
            . self::HOST_REGEX_PATTERN
            . ')/';

        return !(empty($host) || !preg_match($full, $host));
    }

    protected function validatePort(?int $port = null): bool
    {
        if (empty($port)) return true;
        return (1 === preg_match(self::PORT_REGEX_PATTERN, (string)$port));
    }

    protected function validatePath(string $path): bool
    {
        return !(empty($path) || $path[0] !== '/');
    }

    /**
     * Redirect Codes:
     *
     * <code>
     *  301: Moved Permanently
     *
     *    - The requested resource has been assigned a new permanent URI and any
     *      future references to this resource SHOULD use one of the returned URIs.
     *      Clients with link editing capabilities ought to automatically re-link
     *      references to the Request-URI to one or more of the new references
     *      returned by the server, where possible. This response is cacheable
     *      unless indicated otherwise.
     *
     *  302: Found
     *
     *    - The requested resource resides temporarily under a different URI. Since
     *      the redirection might be altered on occasion, the client SHOULD continue to
     *      use the Request-URI for future requests. This response is only cacheable
     *      if indicated by a Cache-Control or Expires header field.
     *
     *  303: See Other
     *
     *    - The response to the request can be found under a different URI and SHOULD
     *      be retrieved using a GET method on that resource. This method exists primarily
     *      to allow the output of a POST-activated script to redirect the user agent
     *      to a selected resource. The new URI is not a substitute reference for
     *      the originally requested resource. The 303 response MUST NOT be cached,
     *      but the response to the second (redirected) request might be cacheable.
     *
     *  304: Not Modified
     *
     *    - If the client has performed a conditional GET request and access is allowed,
     *      but the document has not been modified, the server SHOULD respond with this
     *      status code. The 304 response MUST NOT contain a message-body, and thus is
     *      always terminated by the first empty line after the header fields.
     *
     *  305: Use Proxy
     *
     *    - The requested resource MUST be accessed through the proxy given by the Location
     *      field. The Location field gives the URI of the proxy. The recipient is expected
     *      to repeat this single request via the proxy. 305 responses MUST only be
     *      generated by origin servers.
     *
     *  306: (Unused)
     *
     *    - The 306 status code was used in a previous version of the specification, is
     *      no longer used, and the code is reserved.
     *
     *  307: Temporary Redirect
     *
     *    - The requested resource resides temporarily under a different URI. Since the
     *      redirection MAY be altered on occasion, the client SHOULD continue to use the
     *      Request-URI for future requests. This response is only cacheable if indicated
     *      by a Cache-Control or Expires header field.
     * </code>
     *
     * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     * @see http://edoceo.com/creo/php-redirect.php
     */
    public function redirect(int $code = 302): void
    {
        if (self::isDataScheme($this->spec)) return;

        if (headers_sent()) {
            \Tk\Log::error('Invalid URL Redirect, Headers Already Sent: ' . $this->toString());
            exit();
        }
        switch ($code) {
            case 301:
                // Convert to GET
                header('301: Moved Permanently HTTP/1.1', true, $code);
                break;
            case 302:
                // Conform re-POST
                header('302: Found HTTP/1.1', true, $code);
                break;
            case 303:
                // don't cache, always use GET
                header('303: See Other HTTP/1.1', true, $code);
                break;
            case 304:
                // use cache
                header('304: Not Modified HTTP/1.1', true, $code);
                break;
            case 305:
                header('305: Use Proxy HTTP/1.1', true, $code);
                break;
            case 306:
                header('306: Not Used HTTP/1.1', true, $code);
                break;
            case 307:
                header('307: Temporary Redirect HTTP/1.1', true, $code);
                break;
        }

        // Node: Kept here for debugging
//        if(Config::isDebug()) {
//            $arr = debug_backtrace();
//            $arr = $arr[0];
//            $msg = sprintf('%s REDIRECT `%s` called from %s:%s',
//                $code,
//                $this->__toString(),
//                str_replace(Config::getBasePath(), '', $arr['file'] ?? ''),
//                $arr['line'] ?? 0
//            );
//            \Tk\Log::debug($msg);
//        }

        header("Location: {$this->__toString()}", true, $code);
        exit();
    }
}
