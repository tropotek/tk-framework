<?php
namespace Tk;

/**
 * This object is a wrapper around the $_REQUEST, $_SERVER, $COOKIE global arrays
 * 
 * global variable of PHP use it accordingly
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Request
{
    /* Common request schemas */
    const SCHEME_HTTP = 'http';
    const SCHEME_HTTPS = 'https';

    /**
     * @var Session|array
     */
    protected $session = null;

    /**
     * @var Cookie|array
     */
    protected $cookie = null;
    
    

    /**
     * Request constructor.
     *
     * @param Session|array $session
     * @param Cookie|array $cookie
     */
    function __construct($session = array(), $cookie = array())
    {
        $this->session = $session;
        $this->cookie = $cookie;
        try {   // Need a catch statement here as it could be run outside a try catch.
            $this->sanitize();
        } catch (\Exception $e) {
            error_log(print_r($e->__toString(), true));
        }
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
     * Set a value in the request with the given key
     * If the value is null then the key is unset from the global array
     *
     * @param string $key A key to retrieve the data.
     * @param mixed $value
     * @return $this
     */
    public function set($key, $value)
    {
        $_REQUEST[$key] = $value;
        return $this;
    }

    /**
     * Remove a value from the request global array
     * 
     * @param $key
     * @return $this
     */
    public function delete($key)
    {
        unset($_REQUEST[$key]);
        return $this;
    }

    /**
     * Returns the value of a request parameter as a String,
     * or null if the parameter does not exist.
     *
     * If the key is not found the $_SERVER global is then checked.
     * 
     * @param string $key The parameter name.
     * @return string
     */
    public function get($key)
    {
        if (isset($_REQUEST[$key])) {
            return $_REQUEST[$key];
        }
        // todo: still not sure about this option
        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }
        
        return '';
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
     * Get the session object.
     * 
     * @return array|Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Get the cookie object
     * 
     * @return array|Cookie
     */
    public function getCookie()
    {
        return $this->cookie;
    }

    /**
     * @return Url
     */
    public function getUri()
    {
        return Url::create($_SERVER["REQUEST_URI"]);
    }

    /**
     * Returns the referring \Tk\Url if available.
     *
     * @return \Tk\Url Returns null if there was no referer.
     */
    public function getReferer()
    {
        $referer = $_SERVER['HTTP_REFERER'];
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
        if ($checkProxy && $_SERVER['HTTP_CLIENT_IP'] != null) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else if ($checkProxy && $_SERVER['HTTP_X_FORWARDED_FOR'] != null) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
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
        return ($_SERVER['HTTPS'] == 'on') ? self::SCHEME_HTTPS : self::SCHEME_HTTP;
    }

    /**
     * Is https secure request
     *
     * @return bool
     */
    public function isSecure()
    {
        return ($this->getScheme() === self::SCHEME_HTTPS);
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
     * 
     * note: In general, php://input should be used instead of $HTTP_RAW_POST_DATA.
     * @return string
     * @link http://php.net/manual/en/reserved.variables.httprawpostdata.php
     */
    public function getRawPostData()
    {
        $postdata = file_get_contents("php://input");
        return $postdata;
    }



}
