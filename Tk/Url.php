<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk;

/**
 * A URL class.
 *
 * <b>[[&lt;scheme&gt;://][[&lt;user&gt;[:&lt;password&gt;]@]&lt;host&gt;[:&lt;port&gt;]]][/[&lt;path&gt;][?&lt;query&gt;][#&lt;fragment&gt;]]</b>
 *
 * where:<br/>
 *  o scheme defaults to http
 *  o host defaults to the current host.
 *  o port defaults to 80
 *
 * When using create() or new \Tk\Url() there are three ways to supply the spec path:
 *  o http://www.example.com/path/to/index.html  =>  http://www.example.com/path/to/index.html
 *  o /server/path/to/index.html  =>  http://www.sitehost.com/server/path/to/index.html
 *  o path/to/index.html  =>  http://www.sitehost.com/siteUrl/path/to/index.html
 *
 * For the last example the path will be determined from the Tk_Config::get('system.siteUrl');
 *
 * NOTE: For external URL's the prepend value is not usable,
 *  it will be cleared.
 *
 * @package Tk
 */
class Url extends Object implements \Serializable
{

    /**
     * This is the supplied full/partial url
     * @var string
     */
    protected $spec = '';

    /**
     * The path to prepend to the url. Generally the site Url path
     * @var string
     */
    protected $prepend = '';




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
     * @var string
     */
    protected $port = '80';

    /**
     * @var aray
     */
    protected $query = array();

    /**
     * @var string
     */
    protected $scheme = 'http';

    /**
     * @var string
     */
    protected $user = '';




    /**
     * __construct
     *
     * NOTE: For external URL's the prepend value is not usable,
     *  it will be cleared.
     *
     * @param string $spec The String to parse as a URL
     * @param string $prepend (optional) Must be a path string beginning with a '/' char
     */
    public function __construct($spec, $prepend = null)
    {
        $this->spec = trim($spec);
        $prepend = rtrim($prepend, '\\/');
        if ($prepend) {
            if ($prepend[0] != '/') {
                throw new Exception('Invalid prepend path. Path must begin with `/`: ' . $prepend);
            }
            $this->prepend = $prepend;
        }
        // Only init resource path URL's these are not inited only stored
        if (!preg_match('/^(#|javascript|mailto)/i', $this->spec)) {
            $this->init();
        }
    }

    public function serialize()
    {
        return serialize(array('spec' => $this->spec, 'prepend' => $this->prepend));
    }


    public function unserialize($data)
    {
        $arr = unserialize($data);
        $this->spec = $arr['spec'];
        $this->prepend = $arr['prepend'];
        $this->init();
    }





    /**
     * Create a url, However if a relative path to a file is given
     * the project site root is prepended to the given URL.
     * To avoid relative URL's use 'new \Tk\Url()' not the create() methods.
     *
     * When using create() or new \Tk\Url() there are three ways to supply the spec path:
     *  o http://www.example.com/path/to/index.html  =>  http://www.example.com/path/to/index.html
     *  o /path/to/index.html  =>  http://www.sitehost.com/siteUrl/path/to/index.html     <-- Convert to relative
     *  o path/to/index.html   =>  http://www.sitehost.com/siteUrl/path/to/index.html     <-- Convert to relative
     *
     * If supplying a relative path, the optional prepend paramater can be added to prepend the path
     * of the url, if none supplied the Tk_config::getSitePath() method is used.
     *
     * @param string $spec
     * @param string $prepend If null then \Tk\Config::getSiteUrl()
     * @return Url
     */
    static function create($spec = '', $prepend = null)
    {
        if ($spec instanceof Url) {
            return $spec;
        }
        // Only apply default prepend to local urls
        if ($prepend === null && class_exists('\Tk\Config')) {
            $prepend = Config::getInstance()->getSiteUrl();
        }
        $url = new self($spec, $prepend);
        return $url;
    }

    /**
     * Create a url that prepends the data the data directory to the path.
     * A relative url is created from the path given, prepended with
     * the \Tk\Config::getDataUrl() path.
     *
     * @param string $path
     * @return Url
     */
    static function createDataUrl($path = '')
    {
        return self::create($path, Config::getInstance()->getDataUrl());
    }

    /**
     * Create a user home path url.
     * The spec will be prepended with the users default path
     *
     * @param string $path
     * @return Url
     */
    static function createHomeUrl($path = '')
    {
        return self::create($path, Config::getInstance()->getSiteUrl() . Config::getInstance()->getHomePath());
    }






    /**
     * Initalise the url object
     */
    private function init()
    {
        $spec = $this->spec;
        $host = 'localhost';


        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $this->scheme = 'https';
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
        } else if (isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
        } else if (Config::getInstance()->isCli() && Config::getInstance()->exists('cli.host')) {
            $host = Config::getInstance()->get('cli.host');
        }

        // build spec into URL format
        if (preg_match('/^\/\//i', $spec)) {
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
            // uninitiate prepend path for non local urls.
            if (str_replace('www.', '', $components['host']) != str_replace('www.', '', $host)) {
                $this->prepend = '';
            }

            if (array_key_exists('scheme', $components)) {
                $this->setScheme($components['scheme']);
            }
            if (array_key_exists('host', $components)) {
                $this->setHost($components['host']);
            }
            if (array_key_exists('port', $components)) {
                $this->setPort($components['port']);
                if ($_SERVER['SERVER_PORT'] != 80 && $this->getHost() == $host) {
                    $this->setPort($_SERVER['SERVER_PORT']);
                }
            }
            if (array_key_exists('user', $components)) {
                $this->setUser($components['user']);
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
     * Compare 2 urls by path if $queryString is false
     * or by complete url if $queryString is true.
     *
     * @param \Tk\Url $url
     * @param bool $queryString
     * @return bool
     */
    public function equals($url, $queryString = false)
    {
        if (!$queryString && $this->getPath() == $url->getPath()) {
            return true;
        }
        if ($queryString && $this->toString() == $url->toString()) {
            return true;
        }
        return false;
    }




    /**
     * Get the fragment of the url
     *
     * @return string
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * Set the fragment portion of the url
     *
     * @param string $str
     * @return Url
     */
    public function setFragment($str)
    {
        $this->fragment = urldecode($str);
        return $this;
    }

    /**
     * Set the scheme
     *
     * @param string $scheme
     * @return Url
     */
    public function setScheme($scheme)
    {
        $this->scheme = $scheme;
        return $this;
    }

    /**
     * Get the scheme
     *
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Get the host name
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set the host portion of the url
     *
     * @param string $str
     * @return Url
     */
    public function setHost($str)
    {
        // Todo: validate with regx
        $this->host = $str;
        return $this;
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
     * Set the password portion of the url
     *
     * @param string $str
     * @return Url
     */
    public function setPassword($str)
    {
        // Todo: validate with regx
        $this->password = $str;
        return $this;
    }

    /**
     * Get the url path
     * If the path is a directory the trailing / is removed
     *
     * @return string
     */
    public function getPath($relative = false)
    {
        if ($relative || !$this->prepend) {
            return $this->path;
        }
        $path = $this->prepend . $this->path;
        return $path;
    }

    /**
     * Set the path portion of the url
     *
     * @param string $path
     * @return Url
     */
    public function setPath($path)
    {
        $path = urldecode($path);
        if ($this->prepend) {
            if (preg_match('|^('.  preg_quote($this->prepend) . ')|', $path)) {
                //if ($path == '/') $path = '';
                $path = str_replace($this->prepend, '', $path);
            }
        }
        $this->path = $path;
        return $this;
    }

    /**
     * Get the port of the url
     *
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set the port of the url
     *
     * @param string $str
     * @return Url
     */
    public function setPort($str)
    {
        $this->port = (int)$str;
        return $this;
    }

    /**
     * Get the user
     *
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the user portion of the url
     *
     * @param string $str
     * @return Url
     */
    public function setUser($str)
    {
        $this->user = $str;
        return $this;
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
        if (substr($this->getPath(), -6) == 'tar.gz') {
            return 'tar.gz';
        }
        $pos = strrpos(basename($this->getPath()), '.');
        if ($pos) {
            return substr(basename($this->getPath()), $pos + 1);
        }
        return '';
    }

    /**
     * Get the basename of this url with or without its extension.
     *
     * @return string
     */
    public function getBasename($withExt = true)
    {
        $name = basename($this->getPath());
        if (!$withExt && $this->getExtension()) {
            $name = str_replace('.' . $this->getExtension(), '', $name);
            //$name = str_replace($name, '', '.' + $this->getExtension()); // <<--- What the!!!!
        }
        return $name;
    }

    /**
     * Get the query string of the url
     *
     * @return string
     */
    public function getQueryString()
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
     * Get the array of queryfields in a map
     *
     * @return array
     */
    public function getQueryMap()
    {
        return $this->query;
    }

    /**
     * Get the array of queryfields in a map
     *
     * @param array $map
     * @return Url
     */
    public function setQueryMap($map)
    {
        if ($map != null) {
            $this->query = $map;
        }
        return $this;
    }

    /**
     * clear and reset the querystring
     *
     * @return Url
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
     * @param string $value
     * @return Url
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
     * Remove a field in the querystring
     *
     * @param string $field
     * @return Url
     */
    public function delete($field)
    {
        if (isset($this->query[$field])) {
            unset($this->query[$field]);
        }
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
    }

    /**
     * Check if a query field exists in the array
     *
     * @param string $field
     * @return bool
     */
    public function exists($field)
    {
        return isset($this->query[$field]);
    }

    /**
     * redirect
     * Codes:
     *
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
     *
     *
     * func: redirect($to,$code=307)
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     * @link http://edoceo.com/creo/php-redirect.php
     */
    public function redirect($code = 301)
    {

        if (headers_sent()) {
            throw new \Exception('Invalid URL Redirect, Headers Allready Sent.');
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
        if ($this->getConfig()->isDebug()) {
            $arr = debug_backtrace();
            $arr = $arr[0];
            tklog('\Tk\Url::redirect('.$this->toString().'): called from ' . str_replace($this->getConfig()->getSitePath(), '', $arr['file']) . ' (' . $arr['line'] . ') ');
        }
        header("Location: {$this->toString()}");
        exit();
    }

    /**
     * Return a string representation of this object
     *
     * @param bool $showHost
     * @param bool $showScheme
     * @return string
     */
    public function toString($showHost = true, $showScheme = true)
    {
        if (preg_match('/^(#|javascript|mailto)/i', $this->spec)) {
            return $this->spec;
        }
        $url = '';
        if ($showHost) {
            if ($showScheme) {
                if ($this->getScheme() != '') {
                    $url .= $this->getScheme() . '://';
                }
            } else {
                $url .= '//';
            }

            if ($this->getUser() != '' || $this->getPassword() != '') {
                $url .= $this->getUser() . ':' . $this->getPassword() . '@';
            }
            if ($this->getHost() != '') {
                $url .= $this->getHost();
                if ($this->getPort() != 80) {
                    $url .= ':' . $this->getPort();
                }
            }
        }
        if ($this->getPath() != '') {
            $url .= $this->getPath();
        }
        $query = $this->getQueryString();
        if ($query != '') {
            $url .= '?' . $query;
        }
        if ($this->getFragment() != '') {
            $url .= '#' . $this->getFragment();
        }
        return $url;
    }

}