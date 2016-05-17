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
class Request implements \IteratorAggregate
{    
    
    /* Common request schemas */
    const SCHEME_HTTP = 'http';
    const SCHEME_HTTPS = 'https';

    /**
     * Set this if you want to extend the Request's sanitizer method
     * This will be called after the existing sanitizer method is run.
     * The callback will supply the request as a single argument passed to the callback.
     * 
     * @var callable
     */
    static $sanitizerCallback = null;
    
    

    /**
     * Request constructor.
     */
    function __construct()
    {
        $this->sanitize();
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
        try {   // Need a catch statement here as it could be run outside a try catch.
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
            if (is_callable(static::$sanitizerCallback)) {
                call_user_func_array(static::$sanitizerCallback, array($this));
            } 
        } catch (\Exception $e) {
            error_log(print_r($e->__toString(), true));
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
     * @return Uri
     */
    public function getUri()
    {
        return Uri::create($_SERVER["REQUEST_URI"]);
    }

    /**
     * Returns the referring \Tk\Uri if available.
     *
     * @return null|Uri Returns null if there was no referer.
     */
    public function getReferer()
    {
        $referer = $_SERVER['HTTP_REFERER'];
        if ($referer) {
            return Uri::create($referer);
        }
        return null;
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
        if ($checkProxy && isset($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } else if ($checkProxy && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $_SERVER['REMOTE_ADDR'];
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
        return file_get_contents("php://input");
    }

    
    
    
    // ArrayAccess Interface

    /**
     * Whether a offset exists
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return $this->exists($offset);
    }

    /**
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Offset to set
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Offset to unset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        $this->delete($offset);
    }


    /**
     * IteratorAggregate for iterating over the object like an array.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($_REQUEST);
    }
    
    
}
