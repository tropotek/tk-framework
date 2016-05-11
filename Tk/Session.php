<?php
namespace Tk;

/**
 * Class Session
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Session implements \ArrayAccess
{
    /**
     * Location of the initial session creation data array
     * This should be a unique key that would be hard to clash with
     */
    const KEY_DATA = '_-___SESSION_DATA___-_';
        
    /**
     * @var \Tk\Session\Adapter\Iface
     */
    protected $adapter = null;

    /**
     * @var array|\ArrayAccess
     */
    protected $params = array();

    /**
     * @var \Tk\Request
     */
    protected $request = null;



    /**
     * On first session instance creation, sets up the driver and creates session.
     *
     * $params = array(
     *   'session.name' => '',                  // The session name (Default: md5($_SERVER['SERVER_NAME'])
     *   'session.gc_probability' => 0,         // Garbage collection probability
     *   'session.gc_divisor' => 100,           // Garbage collection divisor
     *   'session.expiration' => 0,             // session expiration (Default: 86400sec)
     *   'session.regenerate' => 0,             // 0 or 1 to regenerate the session at intervals
     *   'session.adapter' => '',               // The classname of the session Adapter
     *   'session.validate' => 'user_agent',    // Session parameters to validate: user_agent, ip_address, expiration.
     * )
     * 
     *
     * @param \Tk\Request $request
     * @param \Tk\Session\Adapter\Iface|null $adapter
     * @param array|\ArrayAccess $params
     */
    private function __construct($request, $adapter = null, $params = array())
    {
        $this->request = $request;
        $this->params = $params;
        
        if (!$this->getParam('session.name'))
            $this->params['session.name'] = md5($_SERVER['SERVER_NAME']);
        if (!$this->getParam('session.expiration'))
            $this->params['session.expiration'] = 86400;
        if ($this->getParam('session.gc_probability'))
            ini_set('session.gc_probability', (int)$this->getParam('session.gc_probability'));
        if ($this->getParam('session.gc_divisor'))
            ini_set('session.gc_divisor', (int)$this->getParam('session.gc_divisor'));
        if ($this->getParam('session.expiration'))
            ini_set('session.gc_maxlifetime', (int)$this->getParam('session.expiration'));

        // Create a new session
        $this->create($adapter);

        if ($this->getParam('session.regenerate') > 0 && ($_SESSION['_total_hits'] % (int)$this->getParam('session.regenerate')) === 0) {
            // Regenerate session id and update session cookie
            $this->regenerate();
        } else {
            // Always update session cookie to keep the session alive
            $this->request->setCookie($this->getParam('session.name'), $_SESSION['_session_id'], time() + (int)$this->getParam('session.expiration'));
        }

        // Make sure that sessions are closed before exiting
        register_shutdown_function(array($this, 'writeClose'));

    }

    /**
     * Create a new session.
     *
     * @param \Tk\Session\Adapter\Iface $adapter
     * @throws Exception
     * @return \Tk\Session\Adapter\Iface
     */
    public function create($adapter = null)
    {
        // Destroy any current sessions
        $this->destroy();
        if ($adapter instanceof Session\Adapter\Iface) {
            $this->adapter = $adapter;
            // Register non-native adapter as the session handler
            session_set_save_handler(array($this->adapter, 'open'), array($this->adapter, 'close'),
                array($this->adapter, 'read'), array($this->adapter, 'write'), array($this->adapter, 'destroy'),
                array($this->adapter, 'gc'));
        }

        // Validate the session name
        if (!preg_match('~^(?=.*[a-z])[a-z0-9_]++$~iD', $this->getParam('session.name'))) {
            throw new Exception('Invalid Session Name: ' . $this->getParam('session.name'));
        }

        // Name the session, this will also be the name of the cookie
        $sesName = $this->getParam('session.name');

        if (isset($this->request[$sesName]) && isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on') {
            session_id($this->request[$sesName]);
        }
        session_name($sesName);

        // Start the session!
        session_start();

        // reset the session cookie expiration
        if ($this->request->cookieExists($sesName)) {
            $this->request->setCookie($sesName, $this->request->get($sesName), time() + (int)$this->getParam('session.expiration'));
        }

        if(!isset($_SESSION[self::KEY_DATA])) {
            $_SESSION[self::KEY_DATA] = array(
                'session_id' => session_id(),
                'user_agent' => $this->request->getUserAgent(),
                'ip_address' => $this->request->getRemoteAddr(),
                'site_referer' => $this->request->getReferer(),
                'total_hits' => 0,
                'last_activity' => 0
            );
        }

        // Increase total hits
        $_SESSION[self::KEY_DATA]['total_hits'] += 1;

        // Validate data only on hits after one
        if ($_SESSION[self::KEY_DATA]['total_hits'] > 1) {
            // Validate the session
            // TODO: Should we just check for all of em?
            foreach ($this->getParam('session.validate') as $valid) {
                switch ($valid) {
                    // Check user agent for consistency
                    case 'user_agent' :
                        if ($_SESSION[self::KEY_DATA][$valid] !== $this->request->getUserAgent())
                            return $this->create();
                        break;
                    // Check ip address for consistency
                    case 'ip_address' :
                        if ($_SESSION[self::KEY_DATA][$valid] !== $this->request->getRemoteAddr())
                            return $this->create();
                        break;
                    // Check expiration time to prevent users from manually modifying it
                    case 'expiration' :
                        if (time() - $_SESSION[self::KEY_DATA]['last_activity'] > ini_get('session.gc_maxlifetime'))
                            return $this->create();
                        break;
                }
            }
        }
        // Update last activity
        $_SESSION[self::KEY_DATA]['last_activity'] = time();
        
        return $this;
    }

    /**
     * Regenerates the global session id.
     *
     */
    public function regenerate()
    {
        if ($this->adapter) {
            // Pass the regenerating off to the driver in case it wants to do anything special
            $_SESSION[self::KEY_DATA]['session_id'] = $this->adapter->regenerate();
        } else {
            // Generate a new session id
            // Note: also sets a new session cookie with the updated id
            session_regenerate_id(true);
            // Update session with new id
            $_SESSION[self::KEY_DATA]['session_id'] = session_id();
        }
        // Get the session name
        $name = session_name();
        if ($this->request->cookieExists($name)) {
            // Change the cookie value to match the new session id to prevent "lag"
            $_COOKIE[$name] = $_SESSION[self::KEY_DATA]['session_id'];
        }
        return $this;
    }

    /**
     * Destroys/deletes the current session.
     *
     *
     */
    public function destroy()
    {
        if (session_id() !== '') {
            // Get the session name
            $name = session_name();
            // Destroy the session
            session_destroy();
            // Re-initialize the array
            $_SESSION = array();
            // Delete the session cookie
            $this->request->deleteCookie($name);
        }
    }

    /**
     * Runs the sys.session_write event, then calls session_write_close.
     *
     */
    public function writeClose()
    {
        static $run = null;
        if ($run === null) {
            $run = TRUE;
            // Close the session
            session_write_close();
        }
    }

    /**
     * Get the session id.
     *
     * @return  string
     */
    public function getId()
    {
        return $_SESSION[self::KEY_DATA]['session_id'];
    }

    /**
     * Return the session id name
     *
     * @return string
     */
    public function getName()
    {
        return session_name();
    }

    /**
     * Binds data to this session, using the name specified.
     *
     * @param string $key A key to retrieve the data
     * @param mixed $value
     * @return $this
     * @throws \Tk\Exception
     */
    public function set($key, $value = null)
    {
        if ($key == self::KEY_DATA) return $this;
        if ($value === null) {
            $this->delete($key);
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
     * @return mixed
     */
    public function get($key)
    {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
    }

    /**
     * Get the $_SESSION array
     *
     * @return array
     */
    public function getAll()
    {
        return $_SESSION;
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
        $this->delete($key);
        return $val;
    }

    /**
     * Unset an element from the session
     *
     * @param string $key
     * @return $this|void
     */
    public function delete($key)
    {
        if ($key != self::KEY_DATA)
            return $this;
        unset($_SESSION[$key]);
        return $this;
    }

    /**
     * Check if a parameter name exists in the request
     *
     * @param string $key
     * @return bool
     */
    public function exists($key)
    {
        return isset($_SESSION[$key]);
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
    
    
}
