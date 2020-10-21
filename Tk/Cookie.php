<?php
namespace Tk;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Cookie implements \ArrayAccess
{

    /**
     *  30 days in seconds (86400*30)
     */
    const DAYS_30_SEC = 2592000;

    /**
     * @var string
     */
    protected $path = '/';
    
    /**
     * @var string
     */
    protected $domain = '';
    
    /**
     * @var bool
     */
    protected $secure = false;
    
    /**
     * @var bool
     */
    protected $httponly = false;


    /**
     * Cookie constructor.
     *
     *
     * @param string $path [optional] <p>
     * The path on the server in which the cookie will be available on.
     * If set to '/', the cookie will be available
     * within the entire domain. If set to
     * '/foo/', the cookie will only be available
     * within the /foo/ directory and all
     * sub-directories such as /foo/bar/ of
     * domain. The default value is the
     * current directory that the cookie is being set in.
     * </p>
     * @param string $domain [optional] <p>
     * The domain that the cookie is available.
     * To make the cookie available on all subdomains of example.com
     * then you'd set it to '.example.com'. The
     * . is not required but makes it compatible
     * with more browsers. Setting it to www.example.com
     * will make the cookie only available in the www
     * subdomain. Refer to tail matching in the
     * spec for details.
     * </p>
     * @param bool $secure [optional] <p>
     * Indicates that the cookie should only be transmitted over a
     * secure HTTPS connection from the client. When set to true, the
     * cookie will only be set if a secure connection exists.
     * On the server-side, it's on the programmer to send this
     * kind of cookie only on secure connection (e.g. with respect to
     * $_SERVER["HTTPS"]).
     * </p>
     * @param bool $httponly [optional] <p>
     * When true the cookie will be made accessible only through the HTTP
     * protocol. This means that the cookie won't be accessible by
     * scripting languages, such as JavaScript. This setting can effectively
     * help to reduce identity theft through XSS attacks (although it is
     * not supported by all browsers). Added in PHP 5.2.0.
     * true or false
     * </p>
     */
    public function __construct($path = '/', $domain = '', $secure = false, $httponly = false)
    {
        if (!$path)
            $path = '/';
        $this->path = $path;
        if (!$domain && !empty($_SERVER['SERVER_NAME'])) {
            $domain = $_SERVER['SERVER_NAME'];
        }
        $this->domain = $domain;
        $this->secure = $secure;
        $this->httponly = $httponly;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return bool
     */
    public function isSecure()
    {
        return $this->secure;
    }

    /**
     * @return bool
     */
    public function isHttponly()
    {
        return $this->httponly;
    }



    /**
     * Returns true if there is a cookie with this name.
     *
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return isset($_COOKIE[$key]);
    }

    /**
     * Get the value of the given cookie. If the cookie does not exist null will be returned.
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return (isset($_COOKIE[$key]) ? $_COOKIE[$key] : null);
    }

    /**
     * Set a cookie. Silently does nothing if headers have already been sent.
     *
     * @param string $key
     * @param string $value
     * @param float|int $expire Expiry time in seconds (Default: 30 Days)
     * @return $this
     */
    public function set($key, $value, $expire = self::DAYS_30_SEC)
    {
        if (!headers_sent()) {
            $expire = $expire + time();
            $r = null;
            if (PHP_VERSION_ID >= 70300) {
                $cfg = [
                    'expires' => $expire,
                    //'path' => $this->path . '; samesite=strict',
                    'path' => $this->path,
                    'domain' => $this->domain,
                    'secure' => $this->secure,
                    'httponly' => $this->httponly,
                    'samesite' => 'strict'
                ];
                $r = @setcookie($key, $value, $cfg);
            } else {
                $r = @setcookie($key, $value, $expire, $this->path . '; samesite=strict', $this->domain, $this->secure, $this->httponly);
            }
            if ($r) {
                $_COOKIE[$key] = $value;
            }
        }
        return $this;
    }

    /**
     * Delete a cookie.
     *
     * @param string $key
     * @param bool $removeGlobal Set to true to remove cookie from the current request global.
     * @return Cookie
     */
    public function delete($key, $removeGlobal = true)
    {
        if (!headers_sent()) {
            if (PHP_VERSION_ID >= 70300) {
                $cfg = [
                    'expires' => -3600,
                    //'path' => $this->path . '; samesite=strict',
                    'path' => $this->path,
                    'domain' => $this->domain,
                    'secure' => $this->secure,
                    'httponly' => $this->httponly,
                    'samesite' => 'strict'
                ];
                setcookie($key, '', $cfg);
            } else {
                setcookie($key, '', -3600, $this->path . '; samesite=strict', $this->domain, $this->secure, $this->httponly);
            }
            if ($removeGlobal) {
                unset($_COOKIE[$key]);
                unset($_REQUEST[$key]);
            }
        }
        return $this;
    }

    
    /**
     * Whether a offset exists
     *
     * @see http://php.net/manual/en/arrayaccess.offsetexists.php
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
        return $this->has($offset);
    }

    /**
     * Offset to retrieve
     *
     * @see http://php.net/manual/en/arrayaccess.offsetget.php
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
     * @see http://php.net/manual/en/arrayaccess.offsetset.php
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
     * @see http://php.net/manual/en/arrayaccess.offsetunset.php
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


}