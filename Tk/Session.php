<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk;

/**
 *
 *
 * @package Tk
 */
class Session
{

    /**
     * @var \Tk\Session
     */
    static $instance = null;

    /**
     * Protected session keys
     * @var array
     */
    protected $protect = array('_session_id', '_user_agent', '_last_activity', '_ip_address', '_total_hits', '_site_referer');

    /**
     * @var \Tk\Session\Adapter\Iface
     */
    protected $adapter = null;

    /**
     * @var array
     */
    protected $config = array();

    /**
     * @var \Tk\Request
     */
    protected $request = null;




    /**
     * On first session instance creation, sets up the driver and creates session.
     *
     *
     */
    private function __construct($config)
    {
        $this->config = $config;
        $this->request = $config->getRequest();

        // Makes a mirrored array, eg: foo=foo
        $this->protect = array_combine($this->protect, $this->protect);

        // Tk_Configure garbage collection
        ini_set('session.gc_probability', (int)$this->config['session.gc_probability']);
        ini_set('session.gc_divisor', (int)$this->config['session.gc_divisor']);
        ini_set('session.gc_maxlifetime', ($this->config['session.expiration'] == 0) ? 86400 : $this->config['session.expiration']);

        // Create a new session
        $adapter = null;
        if ($this->config['session.driver']) {
            $class = $this->config['session.driver'];
            $adapter = new $class();
        }
        $this->create($adapter);

        if ($this->config['session.regenerate'] > 0 && ($_SESSION['_total_hits'] % $this->config['session.regenerate']) === 0) {
            // Regenerate session id and update session cookie
            $this->regenerate();
        } else {
            // Always update session cookie to keep the session alive
            $this->request->setCookie($this->config['session.name'], $_SESSION['_session_id'], time() + $this->config['session.expiration']);
        }

        // Make sure that sessions are closed before exiting
        register_shutdown_function(array($this, 'writeClose'));

        // Singleton instance
        self::$instance = $this;

    }


    /**
     * Get an instance of this object
     *
     * @param array $config
     * @return \Tk\Session
     */
    static function getInstance($config = null)
    {
        if (self::$instance == null) {
            if (!$config) $config = \Tk\Config::getInstance();
            self::$instance = new self($config);
        }
        return self::$instance;
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
            // Validate the driver
            if (!($this->adapter instanceof Session\Adapter\Iface)) {
                throw new Exception('Invalid driver object: ' . get_class($adapter));
            }
            // Register non-native driver as the session handler
            session_set_save_handler(array($this->adapter, 'open'), array($this->adapter, 'close'),
                array($this->adapter, 'read'), array($this->adapter, 'write'), array($this->adapter, 'destroy'),
                array($this->adapter, 'gc'));
        }

        // Validate the session name
        if (!preg_match('~^(?=.*[a-z])[a-z0-9_]++$~iD', $this->config['session.name'])) {
            throw new Exception('Invalid Session Name: ' . $this->config['session.name']);
        }

        // Name the session, this will also be the name of the cookie
        $sesName = $this->config['session.name'];

        if ($this->request->exists($sesName) && isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on') {
            session_id($this->request->get($sesName));
        }
        session_name($sesName);

        // Start the session!
        session_start();

        // reset the session cookie expiration
        if ($this->request->cookieExists($sesName)) {
            $this->request->setCookie($sesName, $this->request->get($sesName), time() + $this->config['session.expiration']);
        }

        // Put session_id in the session variable
        $_SESSION['_session_id'] = session_id();
        if(!isset($_SESSION['_user_agent'])) {
            $_SESSION['_user_agent'] = $this->request->getUserAgent();
            $_SESSION['_ip_address'] = $this->request->getRemoteAddr();
            $_SESSION['_site_referer'] = $this->request->getReferer();
            $_SESSION['_total_hits'] = 0;
        }

        // Increase total hits
        $_SESSION['_total_hits'] += 1;

        // Validate data only on hits after one
        if ($_SESSION['_total_hits'] > 1) {
            // Validate the session
            foreach ($this->config['session.validate'] as $valid) {
                switch ($valid) {
                    // Check user agent for consistency
                    case 'user_agent' :
                        if ($_SESSION['_'.$valid] !== $this->request->getUserAgent())
                            return $this->create();
                        break;
                    // Check ip address for consistency
                    case 'ip_address' :
                        if ($_SESSION['_'.$valid] !== $this->request->getRemoteAddr())
                            return $this->create();
                        break;
                    // Check expiration time to prevent users from manually modifying it
                    case 'expiration' :
                        if (time() - $_SESSION['_last_activity'] > ini_get('session.gc_maxlifetime'))
                            return $this->create();
                        break;
                }
            }
        }
        // Update last activity
        $_SESSION['_last_activity'] = time();
        tklog('Session started: [ID: ' . $this->getId() . ']');
        return $adapter;
    }

    /**
     * Regenerates the global session id.
     *
     */
    public function regenerate()
    {
        if ($this->config['session.driver']) {
            // Pass the regenerating off to the driver in case it wants to do anything special
            $_SESSION['_session_id'] = $this->adapter->regenerate();
        } else {
            // Generate a new session id
            // Note: also sets a new session cookie with the updated id
            session_regenerate_id(true);
            // Update session with new id
            $_SESSION['_session_id'] = session_id();
        }
        // Get the session name
        $name = session_name();
        if ($this->request->cookieExists($name)) {
            // Change the cookie value to match the new session id to prevent "lag"
            $_COOKIE[$name] = $_SESSION['_session_id'];
        }
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
        return $_SESSION['_session_id'];
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
     * @throws \Tk\Exception
     */
    public function set($key, $value = null)
    {
        if ($value === null) {
            $this->delete($key);
        } else {
            if (isset($this->protect[$key]))
                throw new Exception('Protected Session key name used: ' . $key, 1001);
            $_SESSION[$key] = $value;
        }
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
    public function getAllParams()
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
     */
    public function delete($key)
    {
        if (isset($this->protect[$key]))
            return;
        unset($_SESSION[$key]);
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


}
