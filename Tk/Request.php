<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk;

/**
 * This object is a wrapper around the $_REQUEST, $_GET, $_POST, $_SERVER, $_COOKIE
 * global variable of PHP use it accordiningly
 *
 * @package Tk
 */
class Request
{
    /* Common Data Types */
    const SCHEME_HTTP = 'http';
    const SCHEME_HTTPS = 'https';

    /**
     * @var \Tk\Request
     */
    static $instance = null;

    /**
     * Sigleton, No instances can be created.
     * Use:
     *   \Tk\Request::getInstance()
     */
    function __construct()
    {
        // Need a catch statement here as it could be run oiutside a try catch.
        try {
            $this->sanitize();
        } catch (\Exception $e) {
            error_log(print_r($e->__toString(), true));
        }
    }

    /**
     * Get an instance of this object
     *
     * @return \Tk\Request
     */
    static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Sanitize Globals
     *
     * This function does the following:
     *
     * Unsets $_GET data (if query strings are not enabled)
     *
     * Unsets all globals if register_globals is enabled
     *
     * Standardizes newline characters to \n
     *
     * @access	private
     * @return	void
     */
    private function sanitize()
    {

        // Clean $_REQUEST data
        if (is_array($_REQUEST) && count($_REQUEST) > 0) {
            foreach ($_REQUEST as $key => $val) {
                $_REQUEST[$this->cleanKey($key)] = $this->cleanData($val);
            }
        }

        // Clean $_GET data
        if (is_array($_GET) && count($_GET) > 0) {
            foreach ($_GET as $key => $val) {
                $_GET[$this->cleanKey($key)] = $this->cleanData($val);
            }
        }

        // Clean $_POST Data
        if (is_array($_POST) && count($_POST) > 0) {
            foreach ($_POST as $key => $val) {
                $_POST[$this->cleanKey($key)] = $this->cleanData($val);
            }
        }

        // Clean $_COOKIE Data
        if (is_array($_COOKIE) && count($_COOKIE) > 0) {
            foreach ($_COOKIE as $key => $val) {
                $_COOKIE[$this->cleanKey($key)] = $this->cleanData($val);
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Clean Input Data
     *
     * This is a helper function. It escapes data and
     * standardizes newline characters to \n
     *
     * @param	string|array $str
     * @return	string
     * @todo: implement some other fast checks here
     */
    private function cleanData($str)
    {
        if (is_array($str)) {
            $new_array = array();
            foreach ($str as $key => $val) {
                $new_array[$this->cleanKey($key)] = $this->cleanData($val);
            }
            return $new_array;
        }

        // Standardize newlines
        return preg_replace("/\015\012|\015|\012/", "\n", $str);
    }

    // --------------------------------------------------------------------

    /**
     * Clean Keys
     *
     * This is a helper function. To prevent malicious users
     * from trying to exploit keys we make sure that keys are
     * only named with alpha-numeric text and a few other items.
     *
     * @param	string $str
     * @return	string
     * @throws \Tk\Exception
     */
    private function cleanKey($str)
    {
        if (!preg_match("/^[a-z0-9:_\[\]\/-]+$/i", $str)) {
            throw new Exception('Disallowed Key Characters.');
        }
        return $str;
    }









    /**
     * Returns the value from the $_SERVER super global
     *
     * @return string The value or null if key not exists
     */
    public function getServer($key)
    {
        if (array_key_exists($key, $_SERVER)) {
            return $_SERVER[$key];
        }
    }

    /**
     * Returns the referering \Tk\Url if available.
     *
     * @return \Tk\Url Returns null if there was no referer.
     */
    public function getReferer()
    {
        $referer = $this->getServer('HTTP_REFERER');
        if ($referer) {
            return Url::create($referer);
        }
    }

    /**
     * This method will return true if the refering URI host is
     * the same as the request URI host
     *
     * @return bool
     */
    public function checkReferer()
    {
        $referer = $this->getReferer();
        $request = $this->getUri();
        if ($referer && $referer->getHost() == $request->getHost()) {
            return true;
        }
        return false;
    }

    /**
     * Returns the 'REQUEST_URI' which was given in order to access the page.
     *
     * @return  \Tk\Url
     */
    public function getRequestUri()
    {
        static $url = null;
        if (!$url) {
            $urlStr = $this->getServer('REQUEST_URI');
            $scheme = 'http://';
            if ($this->getServer('HTTPS') == 'on') {
                $scheme = 'https://';
            }
            $urlStr = $scheme . $this->getServer('HTTP_HOST') . $urlStr;
            $url = Url::create($urlStr);
        }
        return clone $url;
    }

    /**
     * Alias for getRequestUri
     *
     * @return \Tk\Url
     */
    public function getUri()
    {
        return $this->getRequestUri();
    }

    /**
     * Set a value in the request with the given key
     * If the value is null then the key is unset from the global array
     *
     * @param string $key A key to retrieve the data.
     * @param mixed $value
     */
    public function set($key, $value)
    {
        if ($value === null) {
            unset($_REQUEST[$key]);
        } else {
            $_REQUEST[$key] = $value;
        }
    }

    /**
     * Returns the value of a request parameter as a String,
     * or null if the parameter does not exist.
     *
     * You should only use this method when you are sure the parameter has
     * only one value. If the parameter might have more than one value, use
     * getParameterValues().
     *
     * If you use this method with a multivalued parameter, the value returned
     * is equal to the first value in the array returned by getParameterValues.
     *
     * @param string $key The parameter name.
     * @return mixed
     */
    public function get($key)
    {
        if (isset($_REQUEST[$key])) {
            return $_REQUEST[$key];
        }
    }

    /**
     * Returns an array of String objects containing all of the values the
     * given request parameter has, or null if the parameter does not exist.
     *
     * If the parameter has a single value, the array has a length of 1.
     *
     * @param string $key
     * @return array
     */
    public function getArray($key)
    {
        if (isset($_REQUEST[$key])) {
            if (is_array($_REQUEST[$key])) {
                return $_REQUEST[$key];
            } else {
                return array($_REQUEST[$key]);
            }
        }
    }

    /**
     * Get the $_REQUEST array map
     *
     * @return array
     */
    public function getAll()
    {
        return $_REQUEST;
    }

    /**
     * Returns an array containing the keys contained in
     * this $_REQUEST.
     *
     * @return array
     */
    public function getKeys()
    {
        return array_keys($_REQUEST);
    }

    /**
     * Check if a parameter name exists in the request
     *
     * @param string $key
     * @return bool
     */
    public function exists($key)
    {
        return isset($_REQUEST[$key]);
    }

    /**
     * Get the clients remote hostname if available
     *
     * @return string
     */
    public function getRemoteHost()
    {
        return gethostbyaddr($this->getRemoteAddr());
    }

    /**
     * Get the IP of the clients machine.
     *
     * @param bool $checkProxy
     * @return string
     */
    public function getRemoteAddr($checkProxy = true)
    {
        $ip = '';
        if ($checkProxy && $this->getServer('HTTP_CLIENT_IP') != null) {
            $ip = $this->getServer('HTTP_CLIENT_IP');
        } else if ($checkProxy && $this->getServer('HTTP_X_FORWARDED_FOR') != null) {
            $ip = $this->getServer('HTTP_X_FORWARDED_FOR');
        } else {
            $ip = $this->getServer('REMOTE_ADDR');
        }
        return $ip;
    }

    /**
     * Get the request URI scheme
     *
     * @return string
     */
    public function getScheme()
    {
        return ($this->getServer('HTTPS') == 'on') ? $this->SCHEME_HTTPS : $this->SCHEME_HTTP;
    }

    /**
     * Is https secure request
     *
     * @return bool
     */
    public function isSecure()
    {
        return ($this->getScheme() === $this->SCHEME_HTTPS);
    }

    /**
     * Return the value of the given HTTP header. Pass the header name as the
     * plain, HTTP-specified header name. Ex.: Ask for 'Accept' to get the
     * Accept header, 'Accept-Encoding' to get the Accept-Encoding header.
     *
     * @param string $header HTTP header name
     * @return string|false HTTP header value, or false if not found
     */
    public function getHeader($header)
    {
        // Try to get it from the $_SERVER array first
        $temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
        if (isset($_SERVER[$temp])) {
            return $_SERVER[$temp];
        }

        // This seems to be the only way to get the Authorization header on
        // Apache
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (isset($headers[$header])) {
                return $headers[$header];
            }
            $header = strtolower($header);
            foreach ($headers as $key => $value) {
                if (strtolower($key) == $header) {
                    return $value;
                }
            }
        }
        return false;
    }

    /**
     * Get the browser userAgent string
     *
     * @return string
     */
    public function getUserAgent()
    {
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            return $_SERVER['HTTP_USER_AGENT'];
        }
        return '';
    }

    /**
     * Returns the name of the HTTP method with which this request was made.
     * For example, GET, POST, or PUT.
     *
     * @return string
     */
    public function getRequestMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Returns the raw post data.
     *
     * @return string
     */
    public function rawPostData()
    {
        global $HTTP_RAW_POST_DATA;
        return $HTTP_RAW_POST_DATA;
    }












    /**
     * Returns true if there is a cookie with this name.
     *
     * @param string $key
     * @return bool
     */
    public function cookieExists($key)
    {
        return isset($_COOKIE[$key]);
    }

    /**
     * Get the value of the given cookie. If the cookie does not exist null will be returned.
     *
     * @param string $key
     * @return mixed
     */
    public function getCookie($key)
    {
        return (isset($_COOKIE[$key]) ? $_COOKIE[$key] : null);
    }

    /**
     * Set a cookie. Silently does nothing if headers have already been sent.
     *
     * @param string $key
     * @param string $value
     * @param int $expire
     * @return bool
     */
    public function setCookie($key, $value, $expire = null)
    {
        $config = Config::getInstance();
        $retval = false;
        if (!headers_sent()) {
            if ($expire === null) {
                if ($config->get('cookie.expire') <= 0) {
                    $expire = (86400*365) + time();
                } else {
                    $expire = $config->get('cookie.expire') + time();
                }
            }
            $retval = null;
            $retval = @setcookie($key, $value, $expire, $config->get('cookie.path'), $config->get('cookie.domain'), $config->get('cookie.secure'), $config->get('cookie.httponly'));

            if ($retval) {
                $_COOKIE[$key] = $value;
            }
        }
        return $retval;
    }

    /**
     * Delete a cookie.
     *
     * @param string $key
     * @param bool $removeGlobal Set to true to remove cookie from the current request global.
     * @return bool
     */
    public function deleteCookie($key, $removeGlobal = true)
    {
        $config = Config::getInstance();
        $retval = false;
        if (!headers_sent()) {
            if (version_compare(\PHP_VERSION, '5.2.0', '>')) {
                $retval = setcookie($key, '', -3600, $config->get('cookie.path'), $config->get('cookie.domain'), $config->get('cookie.secure'), $config->get('cookie.httponly'));
            } else {
                $retval = setcookie($key, '', -3600, $config->get('cookie.path'), $config->get('cookie.domain'), $config->get('cookie.secure'));
            }
            if ($removeGlobal) {
                unset($_COOKIE[$key]);
                unset($_REQUEST[$key]);
            }
        }
        return $retval;
    }

}
