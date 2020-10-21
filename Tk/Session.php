<?php
namespace Tk;

/**
 * Class Session
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 * @todo We need to check the session name and sessino ID values are correctly implemented???
 * @todo Cookies are saved for each path dpth from the site path, we need to fix that....
 */
class Session implements \ArrayAccess
{

    /**
     * Location of the initial session creation data array
     * This should be a unique key that would be hard to clash with
     */
    const KEY_DATA = '_-___SESSION_DATA___-_';

    /**
     * @var Session
     */
    public static $instance = null;

    /**
     * @var bool
     */
    private $started = false;
    
    /**
     * @var \Tk\Session\Adapter\Iface
     */
    protected $adapter = null;

    /**
     * @var array|\ArrayAccess
     */
    protected $params = array();

    /**
     * @var array
     */
    protected $data = array();

    /**
     * @var Request
     */
    protected $request = null;

    /**
     * @var Cookie
     */
    protected $cookie = null;


    /**
     * On first session instance creation, sets up the driver and creates session.
     *
     * $params = array(
     *   'session.name' => '',                  // The session name (Default: md5($_SERVER['SERVER_NAME'])
     *   'session.gc_probability' => 0,         // Garbage collection probability
     *   'session.gc_divisor' => 100,           // Garbage collection divisor
     *   'session.gc_maxlifetime' => 0,         // session expiration (Default: 86400sec)
     *   'session.regenerate' => 0,             // 0 or 1 to regenerate the session at intervals
     *   'session.adapter' => '',               // The classname of the session Adapter
     *   'session.validate' => 'user_agent',    // Session parameters to validate: user_agent, ip_address, expiration.
     * )
     *
     * @param null $adapter
     * @param array|\ArrayAccess $params
     * @param Request $request
     * @param Cookie $cookie
     */
    public function __construct($adapter = null, $params = array(), $request = null, $cookie = null)
    {
        $this->params = $params;
        
        if (!$request)
            $request = Request::createFromGlobals();
        if (!$cookie)
            $cookie = new Cookie();

        $this->adapter = $adapter;
        $this->request = $request;
        $this->cookie = $cookie;
        
        if (!$this->getParam('session.name'))
            $this->params['session.name'] = md5($_SERVER['SERVER_NAME']);
        if (!$this->getParam('session.gc_maxlifetime')) {
            $this->params['session.gc_maxlifetime'] = 86400;
        }
        if ($this->getParam('session.gc_probability'))
            ini_set('session.gc_probability', (int)$this->getParam('session.gc_probability'));
        if ($this->getParam('session.gc_divisor'))
            ini_set('session.gc_divisor', (int)$this->getParam('session.gc_divisor'));
        if ($this->getParam('session.save_path'))
            ini_set('session.save_path', $this->getParam('session.save_path'));
        if ($this->getParam('session.gc_maxlifetime'))
            ini_set('session.gc_maxlifetime', $this->getParam('session.gc_maxlifetime'));

        // Start this session
        $this->start();

    }


    /**
     * Get an instance of this object
     *
     * @param null $adapter
     * @param array $params
     * @param null $request
     * @param null $cookie
     * @return Session
     */
    public static function getInstance($adapter = null, $params = array(), $request = null, $cookie = null)
    {
        if (self::$instance == null) {
            self::$instance = new static($adapter, $params, $request, $cookie);
        }
        return self::$instance;
    }
    
    /**
     * Start this session
     *
     * @return Session
     */
    public function start()
    {
        if ($this->started) return $this;   // return if session already started

        // Destroy any existing sessions
        $this->destroy();

        // Setup adapter
        if ($this->adapter instanceof Session\Adapter\Iface) {
            session_set_save_handler(
                array($this->adapter, 'open'), array($this->adapter, 'close'), array($this->adapter, 'read'),
                array($this->adapter, 'write'), array($this->adapter, 'destroy'), array($this->adapter, 'gc')
            );
        }
        // Make sure that sessions are closed before exiting
        register_shutdown_function(array($this, 'writeClose'));

        // Name the session, this will also be the name of the cookie
        $sesName = $this->getParam('session.name');
        if (!preg_match('~^(?=.*[a-z])[a-z0-9_]++$~iD', $sesName)) {
            //throw new Exception('Invalid Session Name: ' . $sesName);
            error_log('Invalid Session Name: ' . $sesName);
        }
        session_name($sesName);
        if ($this->getRequest()->has($sesName) && isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on') {
            session_id($this->getRequest()->get($sesName));
        }
        // Start the session!
        if (PHP_VERSION_ID >= 70300) {
            $s = (int)$this->getCookie()->isSecure();
            $ho = (int)$this->getCookie()->isHttponly();
            $cfg = [
                'lifetime' =>  time() + (int)$this->getParam('session.gc_maxlifetime'),
                'path' => $this->getCookie()->getPath(),
                'domain' => $this->getCookie()->getDomain(),
                'secure' => "$s",
                'httponly' => "$ho",
                'samesite' => 'strict',
                //'samesite' => 'None',
            ];
            session_set_cookie_params($cfg);

        } else {
//            if ($this->getCookie()->isSecure()) {
//                ini_set('session.cookie_secure', 'On');
//            }
            session_set_cookie_params(time() + (int)$this->getParam('session.gc_maxlifetime'),
                $this->getCookie()->getPath() . '; samesite=strict', $this->getCookie()->getDomain(),
                $this->getCookie()->isSecure(), $this->getCookie()->isHttponly());
        }
        session_start();
        $this->started = true;

        // reset the session cookie expiration
        if ($this->getCookie()->has($sesName)) {
            $this->getCookie()->set($sesName, $this->getCookie()->get($sesName), time() + (int)$this->getParam('session.gc_maxlifetime'));
        }

        $referer = $this->getRequest()->getReferer();
        if(!$this->has(self::KEY_DATA)) {
            $this->setData('session_id', $this->getId());
            $this->setData('user_agent', $this->getRequest()->getUserAgent());
            $this->setData('ip_address', $this->getRequest()->getClientIp());
            $this->setData('scheme', $this->getRequest()->server->get('REQUEST_SCHEME', 'http'));
            if ($referer) {
                $this->setData('site_referer', $referer->toString());
                if (!$this->getData('page_referer') || ($this->getData('page_referer') != $referer)) {
                    $this->setData('page_referer', $referer->toString());
                }
            }
            $this->setData('total_hits', 0);
            $this->setData('last_activity', 0);
            $this->set(self::KEY_DATA, $this->data);
        } else {
            $this->data = $this->get(self::KEY_DATA);
            if ($referer) {
                if (!$this->getData('page_referer') || (\Tk\Uri::create()->toString() != $referer->toString())) {
                    $this->setData('page_referer', $referer->toString());
                }
                $this->set(self::KEY_DATA, $this->data);
            }
        }
        // Increase total hits
        $hits = (int)$this->getData('total_hits') + 1;
        $this->setData('total_hits', $hits);

        // Validate data only on hits after one
        if ($hits > 1) {
            // Validate the session, regenerate if not valid.
            foreach ($this->getParam('session.validate') as $valid) {
                switch ($valid) {
                    case 'user_agent' :
                        if ($this->getData($valid) !== $this->getRequest()->getUserAgent())
                            return $this->start();
                        break;
                    case 'scheme' :
                        if ($this->getData($valid) !== $this->getRequest()->server->get('REQUEST_SCHEME', 'http'))
                            return $this->start();
                        break;
                    case 'ip_address' :
                        if ($this->getData($valid) !== $this->getRequest()->getClientIp())
                            return $this->start();
                        break;
                    case 'expiration' :
                        if (time() - $this->getData('last_activity') > ini_get('session.gc_maxlifetime'))
                            return $this->start();
                        break;
                }
            }
        }
        // Update last activity
        $this->setData('last_activity', time());

        if ($this->getParam('session.regenerate') > 0 && ($this->getData('total_hits') % (int)$this->getParam('session.regenerate')) === 0) {
            // Regenerate session id and update session cookie
            $this->regenerate();
        } else {
            // Always update session cookie to keep the session alive
            $this->getCookie()->set($this->getParam('session.name'), $this->getId(), time() + (int)$this->getParam('session.gc_maxlifetime'));
        }

        return $this;
    }

    /**
     * Regenerates the global session id.
     *
     */
    public function regenerate()
    {
        // TODO: we are using the adapter regenerate() function here could we add a callback 'create_id()' to the adapters to do this automatically?
        if ($this->adapter) {
            // Pass the regenerating off to the driver in case it wants to do anything special
            $this->adapter->regenerate();
        } else {
            // Generate a new session id
            // Note: also sets a new session cookie with the updated id
            //session_regenerate_id(true);
            session_regenerate_id(true);
        }
        $this->setData('session_id', session_id());
        // Get the session name
        $name = $this->getName();
        if ($this->getCookie()->has($name)) {    // Change the cookie value to match the new session id to prevent "lag"
            $this->getCookie()->set($name, $this->getId(), time() + (int)$this->getParam('session.gc_maxlifetime'));
        }
        return $this;
    }

    /**
     * Destroys/deletes the current session.
     */
    public function destroy()
    {
        if ($this->getId() !== '') {
            if (session_status() == PHP_SESSION_NONE) {
                    session_start();
            }
            // Re-initialize the array
            $_SESSION = array();
            // Delete the session cookie
            $this->getCookie()->delete($this->getName());
            // Destroy the session
            session_destroy();
            \Tk\Log::alert('Session Destroyed');
        }
    }

    /**
     * Runs the sys.session_write event, then calls session_write_close.
     *
     */
    public function writeClose()
    {
        static $run = null;
        // Close the session
        if ($run === null) {
            $run = TRUE;
            session_write_close();
        }
    }

    /**
     * @param $key
     * @return mixed|string
     */
    public function getData($key)
    {
        if (isset($this->data[$key]))
            return $this->data[$key];
        return '';
    }

    /**
     * get the page referring URL
     *
     * @return Uri
     */
    public function getBackUrl()
    {
        return \Tk\Uri::create($this->getData('page_referer'));
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setData($key, $value)
    {
        $this->data[$key] = $value;
        $this->set(self::KEY_DATA, $this->data);
        return $this;
    }
    
    /**
     * looks for the $key in the params object,
     * if not found then prepends 'session.' to the key
     *
     * return null if not found.
     *
     * @param $key
     * @param string $default
     * @return null|string
     */
    protected function getParam($key, $default = '')
    {
        if (!preg_match('/^session\./i', $key)) {
            $key = 'session.'.$key;
        }
        if (isset($this->params[$key]))
            return $this->params[$key];
        return $default;
    }

    /**
     * Get the session param config list
     * 
     * @return array|\ArrayAccess
     */
    public function getParamList()
    {
        return $this->params;
    }

    /**
     * Get the session id.
     * Changes on re-generation
     *
     * @return  string
     */
    public function getId()
    {
        return session_id();
    }

    /**
     * Return the session name
     * Does not change on regeneration
     *
     * @return string
     */
    public function getName()
    {
        return session_name();
    }

    /**
     * @return Cookie
     */
    public function getCookie()
    {
        return $this->cookie;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }



    /**
     * Returns the data bound with the specified name in this session,
     * or null if data is bound under the name.
     * Once returned removes the data from the session
     *
     * @param string $key The key to retrieve the data.
     * @return mixed
     */
    public function getOnce($key)
    {
        $val = $this->get($key);
        $this->remove($key);
        return $val;
    }

    /**
     * Binds data to this session, using the name specified.
     *
     * @param string $key A key to retrieve the data
     * @param mixed $value
     * @return Session
     */
    public function set($key, $value = null)
    {
        //if ($key == self::KEY_DATA) return $this;
        if ($value === null) {
            $this->remove($key);
        } else {
            $_SESSION[$key] = $value;
        }
        return $this;
    }

    /**
     * Returns the data bound with the specified name in this session,
     * or null if data is bound under the name.
     *
     * @param string $key The key to retrieve the data.
     * @param null|mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
        return $default;
    }

    /**
     * Get all first level array keys of the session
     *
     * @return array
     */
    public function keys()
    {
        return array_keys($_SESSION);
    }

    /**
     * Get the $_SESSION array
     *
     * @return array
     * @deprecated use Session::all()
     */
    public function getAll()
    {
        return $this->all();
    }
    public function all()
    {
        return $_SESSION;
    }

    /**
     * Unset an element from the session
     *
     * @param string $key
     * @return $this
     */
    public function remove($key)
    {
        if ($key != self::KEY_DATA)
            unset($_SESSION[$key]);
        return $this;
    }

    /**
     * Unset an element from the session
     *
     * @param string $key
     * @return $this
     * @deprecated Use remove()
     */
    public function delete($key)
    {
        return $this->remove($key);
    }

    /**
     * Check if a parameter name exists in the request
     *
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return isset($_SESSION[$key]);
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
        $this->remove($offset);
    }
    
    
}
