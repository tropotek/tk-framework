<?php
namespace Tk;


/** A URI class.
 *
 * <b>[[&lt;scheme&gt;://][[&lt;user&gt;[:&lt;password&gt;]@]&lt;host&gt;[:&lt;port&gt;]]][/[&lt;path&gt;][?&lt;query&gt;][#&lt;fragment&gt;]]</b>
 *
 * Where:
 *
 * - __scheme__ defaults to http
 * - __host__ defaults to the current host
 * - __port__ defaults to 80
 *
 * <code>
 * echo Uri::create('/full/uri/path/index.html')->__toString();
 * // Result:
 * //  http://localhost/full/uri/path/index.html
 * </code>
 *
 * If the static $BASE_URL_PATH is set this will be prepended to all relative paths
 * when creating a URI
 * 
 * @todo This object methods should all be mutable...
 * 
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @see http://www.php-fig.org/psr/psr-7/#3-5-psr-http-message-uriinterface
 * @license Copyright 2007 Michael Mifsud
 */
class Uri implements \Serializable, \IteratorAggregate
{
    /**
     * @var string
     */
    public static $BASE_URL_PATH = '';

    const SCHEME_HTTP = 'http';
    const SCHEME_HTTP_SSL = 'https';
    const SCHEME_FTP = 'ftp';

    /**
     * This is the supplied full/partial uri
     * @var string
     */
    protected $spec = '';

    /**
     * @var string
     */
    protected $fragment = '';

    /**
     * @var string
     */
    protected $host = '';

    /**
     * @var string
     */
    protected $password = '';

    /**
     * @var string
     */
    protected $path = '';

    /**
     * @var int
     */
    protected $port = 80;

    /**
     * @var array
     */
    protected $query = array();

    /**
     * @var string
     */
    protected $scheme = 'http';

    /**
     * @var string
     */
    protected $username = '';



    /**
     * Paths that do not start with a scheme section to the uri are prepended with the  self::$BASE_URL . '/' string
     *
     * @param string $spec The String to parse as a URL
     */
    public function __construct($spec = null)
    {
        $this->spec = $spec;
        if ($spec === null) {   // Create an auto request uri.
            $spec = '/';
            if (isset($_SERVER['REQUEST_URI'])) {
                $spec = $_SERVER['REQUEST_URI'];
                if (!empty($_SERVER['QUERY_STRING']) && strstr($spec, '?') === false) {
                    $spec .= '?' . $_SERVER['QUERY_STRING'];
                }
            }
        }
        if ($this->isUrl()) {
            $spec = trim(urldecode($spec));
            if ($spec && self::$BASE_URL_PATH) {
                $p = parse_url($spec);
                //if (!preg_match('/^\/\//', $spec) && !preg_match('/^(#|javascript|mailto|data)/i', $spec) && !isset($p['scheme'])) {
                if (!preg_match('/^\/\//', $spec) && !isset($p['scheme'])) {
                    if (self::$BASE_URL_PATH) {
                        if (preg_match('/^' . preg_quote(self::$BASE_URL_PATH, '/') . '/', $spec)) {
                            $spec = preg_replace('/^' . preg_quote(self::$BASE_URL_PATH, '/') . '/', '', $spec);
                        }
                        //$spec = str_replace(self::$BASE_URL_PATH, '', $spec);
                        $spec = trim($spec, '/');
                        $spec = self::$BASE_URL_PATH . '/' . $spec;
                    }
                }
            }
        }
        $this->spec = $spec;
        $this->init();
    }

    /**
     * A static factory method to facilitate inline calls
     *
     * <code>
     *   \Tk\Uri::create('http://example.com/test');
     * </code>
     *
     * @param $spec
     * @return static
     */
    public static function create($spec = null)
    {
        if ($spec instanceof Uri)
            return clone $spec;
        return new static($spec);
    }


    public function serialize()
    {
        return serialize(array('spec' => $this->spec));
    }
    
    public function unserialize($data)
    {
        $arr = unserialize($data);
        $this->spec = $arr['spec'];
        $this->init();
    }

    /**
     * Initialise the uri object
     */
    private function init()
    {
        $spec = $this->spec;
        $host = 'localhost';

        if (!$this->isUrl()) {
            return;
        }

        //vd($_SERVER['HTTPS'], $_SERVER['REQUEST_SCHEME'], $_SERVER['SERVER_PORT']);
        if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ||
            (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == self::SCHEME_HTTP_SSL) ||
            (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT']))
        {
        //if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $this->scheme = self::SCHEME_HTTP_SSL;
        }

        if (\Tk\Config::getInstance()->get('site.host')) {
            $host = \Tk\Config::getInstance()->get('site.host');
        } else if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
        } else if (isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
        }

        // build spec into URL format
        if (preg_match('/^\/\//', $spec)) {
            $spec = $this->scheme . ':' . $spec;
        }
        if (preg_match('/^www\./i', $spec)) {
            $spec = 'http://' . $spec;
        }
        if (!preg_match('/^([a-z]{3,8}:\/\/)/i', $spec)) {
            if ($spec && $spec[0] != '/') {
                $spec = '/'.$spec;
            }
            $spec =  $this->scheme.'://'.$host.$spec;
        }

        $components = parse_url($spec);
        if ($components) {
            if (array_key_exists('scheme', $components)) {
                $this->setScheme($components['scheme']);
            }
            if (array_key_exists('host', $components)) {
                $this->setHost($components['host']);
            }
            if (array_key_exists('port', $components)) {
                $this->setPort($components['port']);
            } else if (isset($_SERVER['SERVER_PORT']) &&
                ($_SERVER['SERVER_PORT'] != 80 || $this->scheme != 'http') &&
                ($_SERVER['SERVER_PORT'] != 443 || $this->scheme != 'https') &&
                $this->getHost() == $host)
            {
                $this->setPort($_SERVER['SERVER_PORT']);
            }
            if (array_key_exists('user', $components)) {
                $this->setUsername($components['user']);
            }
            if (array_key_exists('pass', $components)) {
                $this->setPassword($components['pass']);
            }
            if (array_key_exists('path', $components)) {
                $this->setPath($components['path']);
            }
            if (array_key_exists('query', $components)) {
                $components['query'] = html_entity_decode($components['query']);
                parse_str($components['query'], $this->query);
            }
            if (array_key_exists('fragment', $components)) {
                $this->setFragment($components['fragment']);
            }
        }
    }

    /**
     * returns true if the uri is a link/URL and not a data/script type uri
     *
     * @return bool
     */
    public function isUrl()
    {
        return !preg_match('/^(#|javascript|mailto|data)/i', $this->spec);
    }

    /**
     * Compare 2 uris by path if $queryString is false
     * or by complete uri if $queryString is true.
     *
     * @param \Tk\Uri $uri
     * @param bool $queryString
     * @return bool
     */
    public function equals($uri, $queryString = false)
    {
        if (!$queryString && $this->getPath() == $uri->getPath()) {
            return true;
        }
        if ($queryString && $this->toString() == $uri->toString()) {
            return true;
        }
        return false;
    }


    /**
     * Get the user
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Get the password if available
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Returns file extension for this pathname.
     *
     * A the last period ('.') in the pathname is used to delimit the file
     * extension .If the pathname does not have a file extension null is
     * returned.
     *
     * @return string
     */
    public function getExtension()
    {
        if ($this->isUrl()) {
            if (substr($this->getPath(), -6) == 'tar.gz') {
                return 'tar.gz';
            }
            return pathinfo($this->getPath(), PATHINFO_EXTENSION);
        }
        return '';
    }

    /**
     * Get the basename of this uri with or without its extension.
     *
     * @return string
     * @deprecated Use Uri::basename();
     */
    public function getBasename()
    {
        return $this->basename();
    }

    /**
     * Get the basename of this uri with or without its extension.
     *
     * @return string
     */
    public function basename()
    {
        return basename($this->getPath());
    }

    /**
     * Get the basename of this uri with or without its extension.
     *
     * @return Uri
     */
    public function dirname()
    {
        $url = clone $this;
        if ($this->isUrl()) {
            $url->spec = dirname($url->getPath());
            $url->setPath(dirname($url->getPath()));
        }
        return $url;
    }

    /**
     * clear and reset the query string
     *
     * @return static
     */
    public function reset()
    {
        $this->query = array();
        return $this;
    }

    /**
     * Add a field to the query string
     *
     * @param string $field
     * @param string|string[] $value
     * @return static
     */
    public function set($field, $value = null)
    {
        if ($value === null) {
            $value = $field;
        }
        $this->query[$field] = $value;
        return $this;
    }

    /**
     * Get a value from the query string.
     *
     * @param string $field
     * @return string
     */
    public function get($field)
    {
        if (isset($this->query[$field])) {
            return $this->query[$field];
        }
        return '';
    }

    /**
     * Get all the query params
     *
     * @return array
     */
    public function all()
    {
        return $this->query;
    }

    /**
     * Check if a query field exists in the array
     *
     * @param string $field
     * @return bool
     */
    public function has($field)
    {
        return isset($this->query[$field]);
    }

    /**
     * Remove a field in the query string
     *
     * @param string $field
     * @return static
     */
    public function remove($field)
    {
        if ($this->has($field)) {
            unset($this->query[$field]);
        }
        return $this;
    }

    /**
     * Remove a field in the query string
     *
     * @param string $field
     * @return static
     * @deprecated Use remove($field)
     */
    public function delete($field)
    {
        return $this->remove($field);
    }

    /**
     * IteratorAggregate for iterating over the object like an array.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->query);
    }

    /**
     * Set the fragment portion of the uri
     *
     * @param string $fragment
     * @return static
     */
    public function setFragment($fragment)
    {
        $this->fragment = urldecode($fragment);
        return $this;
    }

    /**
     * Set the port of the uri
     *
     * @param int $port
     * @return static
     */
    public function setPort($port)
    {
        $port = (int)$port;
        if ($port && ($port <= 0 || $port >= 65535)) {
            \Tk\Log::alert('Invalid port, valid values are 1-65535.');
            $port = null;
        }
        if ($port == 80) {
            $port = null;
        }
        $this->port = $port;
        return $this;
    }

    /**
     * Set the scheme
     *
     * @param string $scheme
     * @return static
     */
    public function setScheme($scheme)
    {
        $this->scheme = $scheme;
        return $this;
    }

    /**
     * Set the host portion of the uri
     *
     * @param string $host
     * @return static
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Set the path portion of the uri
     *
     * @param string $path
     * @return static
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Set the password portion of the uri
     *
     * @param string $password
     * @return static
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Set the user portion of the uri
     *
     * @param string $username
     * @return static
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }
    
    /**
     * Retrieve the authority component of the URI.
     *
     * If no authority information is present, this method MUST return an empty
     * string.
     *
     * The authority syntax of the URI is:
     *
     * <pre>
     * [user-info@]host[:port]
     * </pre>
     *
     * If the port component is not set or is the standard port for the current
     * scheme, it SHOULD NOT be included.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.2
     * @return string The URI authority, in "[user-info@]host[:port]" format.
     */
    public function getAuthority()
    {
        $str = '';
        if (!$this->getHost()) {
            return $str;
        }
        $str .= $this->getUserInfo();
        $str .= $this->getHost();   
        if ($this->getPort() && $this->getPort() != 80) {
            $str .= ':' . $this->getPort();
        }
        return $str;
    }

    /**
     * Retrieve the user information component of the URI.
     *
     * If no user information is present, this method MUST return an empty
     * string.
     *
     * If a user is present in the URI, this will return that value;
     * additionally, if the password is also present, it will be appended to the
     * user value, with a colon (":") separating the values.
     *
     * The trailing "@" character is not part of the user information and MUST
     * NOT be added.
     *
     * @return string The URI user information, in "username[:password]" format.
     */
    public function getUserInfo()
    {
        $str = '';
        if ($this->getUsername()) {
            $str .= $this->getUsername();
        }
        if ($this->getPassword()) {
            $str .= ':' . $this->getPassword();
        }
        return $str;
    }

    /**
     * Retrieve the query string of the URI.
     *
     * If no query string is present, this method MUST return an empty string.
     *
     * The leading "?" character is not part of the query and MUST NOT be
     * added.
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.4.
     *
     * As an example, if a value in a key/value pair of the query string should
     * include an ampersand ("&") not intended as a delimiter between values,
     * that value MUST be passed in encoded form (e.g., "%26") to the instance.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.4
     * @return string The URI query string.
     */
    public function getQuery()
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
        $query = substr($query, 0, -1);
        return $query;
    }

    /**
     * Return the query name value pairs array
     *
     * @return array
     */
    public function getQueryArray() {
        return $this->query;
    }
        
    /**
     * Retrieve the fragment component of the URI.
     *
     * If no fragment is present, this method MUST return an empty string.
     *
     * The leading "#" character is not part of the fragment and MUST NOT be
     * added.
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.5.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.5
     * @return string The URI fragment.
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * Retrieve the scheme component of the URI.
     *
     * If no scheme is present, this method MUST return an empty string.
     *
     * The value returned MUST be normalized to lowercase, per RFC 3986
     * Section 3.1.
     *
     * The trailing ":" character is not part of the scheme and MUST NOT be
     * added.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.1
     * @return string The URI scheme.
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Retrieve the host component of the URI.
     *
     * If no host is present, this method MUST return an empty string.
     *
     * The value returned MUST be normalized to lowercase, per RFC 3986
     * Section 3.2.2.
     *
     * @see http://tools.ietf.org/html/rfc3986#section-3.2.2
     * @return string The URI host.
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Retrieve the path component of the URI.
     *
     * The path can either be empty or absolute (starting with a slash) or
     * rootless (not starting with a slash). Implementations MUST support all
     * three syntaxes.
     *
     * Normally, the empty path "" and absolute path "/" are considered equal as
     * defined in RFC 7230 Section 2.7.3. But this method MUST NOT automatically
     * do this normalization because in contexts with a trimmed base path, e.g.
     * the front controller, this difference becomes significant. It's the task
     * of the user to handle both "" and "/".
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.3.
     *
     * As an example, if the value should include a slash ("/") not intended as
     * delimiter between path segments, that value MUST be passed in encoded
     * form (e.g., "%2F") to the instance.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.3
     * @return string The URI path.
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * If the $BASE_URL is set the path is returned with the $BASE_URL removed.
     * 
     * @return mixed|string
     */
    public function getRelativePath()
    {
        $path = $this->getPath();
        $path = urldecode($path);
        if (preg_match('/^'.  preg_quote(self::$BASE_URL_PATH, '/') . '/', $path)) {
            $path = preg_replace('/^'.preg_quote(self::$BASE_URL_PATH, '/').'/', '', $path);
        }
        //$path = str_replace(self::$BASE_URL_PATH, '', $path);
        return $path;
    }

    /**
     * Retrieve the port component of the URI.
     *
     * If a port is present, and it is non-standard for the current scheme,
     * this method MUST return it as an integer. If the port is the standard port
     * used with the current scheme, this method SHOULD return null.
     *
     * If no port is present, and no scheme is present, this method MUST return
     * a null value.
     *
     * If no port is present, but a scheme is present, this method MAY return
     * the standard port for that scheme, but SHOULD return null.
     *
     * @return null|int The URI port.
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Return a string representation of this object without the dev path
     *
     * @param bool $showHost
     * @param bool $showScheme
     * @return string
     */
    public function toRelativeString($showHost = true, $showScheme = true)
    {
        return $this->toString($showHost, $showScheme, true);
    }

    /**
     * Return a string representation of this object
     *
     * @param bool $showHost
     * @param bool $showScheme
     * @param bool $relativePath
     * @return string
     */
    public function toString($showHost = true, $showScheme = true, $relativePath = false)
    {
        if (!$this->isUrl()) {
            return $this->spec;
        }
        $uri = '';
        if ($showHost) {
            if ($showScheme) {
                if ($this->getScheme() != '') {
                    $uri .= $this->getScheme() . '://';
                }
            } else {
                $uri .= '//';
            }
            $uri .= $this->getAuthority();
        }
        if ($this->getPath() != '') {
            if ($relativePath)
                $uri .= $this->getRelativePath();
            else
                $uri .= $this->getPath();
        }
        $query = $this->getQuery();
        if ($query != '') {
            $uri .= '?' . $query;
        }
        if ($this->getFragment() != '') {
            $uri .= '#' . $this->getFragment();
        }
        return $uri;
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
     * @param int $code
     */
    public function redirect($code = 302)
    {
        if (!$this->isUrl()) return;
        if (headers_sent()) {
            \Tk\Log::error('Invalid URL Redirect, Headers Already Sent.');
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
                // dont cache, always use GET
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

        $arr = debug_backtrace();
        $arr = $arr[0];
        \Tk\Log::notice($code . ' REDIRECT `'.$this->toString().'` Called ' . str_replace(\Tk\Config::getInstance()->getSitePath(), '', $arr['file']) . ':' . $arr['line']);

        /*CLOSE THE SESSION WITH USER DATA*/
        session_write_close();
        header("Location: {$this->toString()}", true, $code);
        exit();
    }

    /**
     * Return the string representation as a URI reference.
     *
     * Depending on which components of the URI are present, the resulting
     * string is either a full URI or relative reference according to RFC 3986,
     * Section 4.1. The method concatenates the various components of the URI,
     * using the appropriate delimiters:
     *
     * - If a scheme is present, it MUST be suffixed by ":".
     * - If an authority is present, it MUST be prefixed by "//".
     * - The path can be concatenated without delimiters. But there are two
     *   cases where the path has to be adjusted to make the URI reference
     *   valid as PHP does not allow to throw an exception in __toString():
     *     - If the path is rootless and an authority is present, the path MUST
     *       be prefixed by "/".
     *     - If the path is starting with more than one "/" and no authority is
     *       present, the starting slashes MUST be reduced to one.
     * - If a query is present, it MUST be prefixed by "?".
     * - If a fragment is present, it MUST be prefixed by "#".
     *
     * @see http://tools.ietf.org/html/rfc3986#section-4.1
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
    
}

///**
// * Class Url
// *
// * @author Michael Mifsud <info@tropotek.com>
// * @see http://www.tropotek.com/
// * @license Copyright 2016 Michael Mifsud
// * @deprecated Use \Tk\Uri::create
// */
//class Url extends Uri {}