<?php
namespace Tk;


/**
 * A Config class for handling the applications dependency values.
 *
 * It can be used as a standard array it extends the \Tk\Registry
 * Example usage:
 * <code>
 * $request = Request::createFromGlobals();
 * $cfg = \Tk\Config::getInstance();
 *
 * $cfg->setAppPath($appPath);
 * $cfg->setRequest($request);
 * $cfg->setAppUrl($request->getBasePath());
 * $cfg->setAppDataPath($cfg->getAppPath().'/data');
 * $cfg->setAppCachePath($cfg->getAppDataPath().'/cache');
 * $cfg->setAppTempPath($cfg->getAppDataPath().'/temp');
 * // Useful for dependency management to create application objects
 * $cfg->setStdObject(function($test1, $test2, $test3) {
 *     $cfg = \Tk\Registry::getInstance();
 *     $obj = new \stdClass();
 *     $obj->test1 = $test1;
 *     $obj->test2 = $test1;
 *     $obj->test3 = $test1;
 *     return $obj;
 * });
 *
 * $var = $cfg->getStdObject('test param', 'test2', 'test3');
 * // or
 * $var = $cfg->createStdObject('test param', 'test2', 'test3');
 * // or
 * $var = $cfg->isStdObject('test param', 'test2', 'test3');
 * var_dump($var);
 *
 *
 *  // Output:
 *  //  object(stdClass)[15]
 *  //      public 'test1' => string 'test param' (length=10)
 *  //      public 'test2' => string 'test param' (length=10)
 *  //      public 'test3' => string 'test param' (length=10)
 *
 *  // The following returns the closure object not the result
 *
 * $var = $cfg->get('std.object');
 * var_dump($var);
 *
 * // Output
 * // object(Closure)[14]
 *
 * </code>
 *
 * Internally the Config values are stored in an array. So to set a value there is a couple of ways to do this:
 *
 * $cfg->setSitePath($path);
 *
 * same as
 *
 * $cfg['site.path'] = $path;
 *
 * To get a values stored in the registry you can do the following using the array access methods:
 *
 * $val = $cfg->getSitePath();
 *
 * same as
 *
 * $val = $cfg['site.path'];
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
class Config extends ArrayObject
{

    /**
     * @var Config
     */
    static $instance = null;


    /**
     * Get an instance of this object
     *
     * @param string $appUrl Only required on first call to init the config paths
     * @param string $appPath
     * @return Config
     */
    static function getInstance($appUrl = '', $appPath = '')
    {
        if (static::$instance == null) {
            static::$instance = new static($appUrl, $appPath);
        }
        return static::$instance;
    }

    /**
     * Construct the config object and initiate default settings
     *
     * @param string $appUrl
     * @param string $appPath
     */
    public function __construct($appUrl = '', $appPath = '')
    {
        parent::__construct();
        $this->init($appUrl, $appPath);
    }

    /**
     * init the default params.
     *
     * @param string $appUrl
     * @param string $appPath
     */
    protected function init($appUrl = '', $appPath = '')
    {
        $this->setAppScripTime(microtime(true));
        $this->setConfig($this);

        // Setup isCli function in config.
        $this->setCli(false);
        if (substr(php_sapi_name(), 0, 3) == 'cli') {
            $this->setCli(true);
        }

        $this->setDebug(false);

        // Setup the app path if none exists
        if (!$appUrl) {
            $appUrl = dirname($_SERVER['PHP_SELF']);
        }
        $appUrl = rtrim($appUrl, '/');
        $this->setAppUrl($appUrl);
        if (!$appPath) {
            $appPath = rtrim(dirname(dirname(dirname(dirname(__DIR__)))), '/');
        }
        $this->setAppPath($appPath);

        $this->setSystemLogPath(ini_get('error_log'));
        $this->setSystemLogLevel('error');

        $this->setDataPath($this->getAppPath() . '/data');
        $this->setDataUrl($this->getAppUrl() . '/data');

        $this->setVendorPath($this->getAppPath() . '/vendor');
        $this->setVendorUrl($this->getAppUrl() . '/vendor');

        $this->setSrcPath($this->getAppPath() . '/src');
        $this->setSrcUrl($this->getAppUrl() . '/src');

        $this->setCachePath($this->getDataPath() . '/cache');
        $this->setCacheUrl($this->getDataUrl() . '/cache');

        $this->setTempPath($this->getDataPath() . '/temp');
        $this->setTempUrl($this->getDataUrl() . '/temp');

        // Site information
        $this->setSystemName('Untitled Site');
        $this->setSystemDescription('');
        $this->setSystemVersion('0.0');
        $this->setSystemLicence('');
        $this->setSystemReleased('');
        
        if (is_file($this->getAppPath() . '/composer.json')) {
            $composer = json_decode(file_get_contents($this->getAppPath() . '/composer.json'));
            $this->setSystemName($composer->name);
            $this->setSystemDescription($composer->description);
            $this->setSystemVersion($composer->version);
            $this->setSystemLicence($composer->license);
            $this->setSystemReleased($composer->time);
        }
        
        
        
    }

    /**
     * @return string
     */
    public function getAppUrl()
    {
        return $this->get('app.url');
    }

    /**
     * @return string
     */
    public function getAppPath()
    {
        return $this->get('app.path');
    }

    /**
     * @return string
     */
    public function getDataUrl()
    {
        return $this->get('data.url');
    }

    /**
     * @return string
     */
    public function getDataPath()
    {
        return $this->get('data.path');
    }

    /**
     * @return string
     */
    public function getVendorUrl()
    {
        return $this->get('vendor.url');
    }

    /**
     * @return string
     */
    public function getVendorPath()
    {
        return $this->get('vendor.path');
    }

    /**
     * @return string
     */
    public function getSrcUrl()
    {
        return $this->get('src.url');
    }

    /**
     * @return string
     */
    public function getSrcPath()
    {
        return $this->get('src.path');
    }

    /**
     * @return string
     */
    public function getCacheUrl()
    {
        return $this->get('cache.url');
    }

    /**
     * @return string
     */
    public function getCachePath()
    {
        return $this->get('cache.path');
    }

    /**
     * @return string
     */
    public function getTempUrl()
    {
        return $this->get('temp.url');
    }

    /**
     * @return string
     */
    public function getTempPath()
    {
        return $this->get('temp.path');
    }

    /**
     * Is this application a command run in a terminal
     * @return boolean
     */
    public function getCli()
    {
        return $this->get('temp.path');
    }

    /**
     * Get the system DB object
     *
     * @return Db\Pdo|mixed
     */
    public function getDb()
    {
        return $this->get('db');
    }

    /**
     * Set the system DB object
     *
     * @param Db\Pdo|mixed $db
     * @return $this
     */
    public function setDb($db)
    {
        $this->set('db', $db);
        return $this;
    }







    /**
     * Import params from another registry object or array
     *
     * @param Registry|array $params
     * @return $this
     */
    public function import($params)
    {
        foreach($params as $k => $v) {
            $this[$k] = $v;
        }
        return $this;
    }

    /**
     * Allow call to parameters via a get and set
     *
     * For example if the following entries exist in the registry:
     *
     *   array(
     *    'site.path' => '/path/to/site',
     *    'site.url' => '/url/to/site'
     * )
     *
     * Then they can be accessed by the following virtual methods:
     *
     *   $registry->getSitePath();
     *   $registry->setSitePath('/');
     *
     * @param string $func
     * @param array  $argv
     * @return mixed | null
     */
    public function __call($func, $argv)
    {
        $key = preg_replace('/[A-Z]/', '.$0', $func);
        $key = strtolower($key);

        $pos = strpos($key, '.');
        $type = substr($key, 0, $pos);
        $key = substr($key, $pos+1);

        if ($type == 'set') {
            $this->set($key, $argv[0]);
        } else if ($type == 'get' || $type = 'create' | $type = 'is' | $type = 'has') {
            $val = $this->get($key);
            if ($val instanceof \Closure) {
                return call_user_func_array($val, $argv);
            }
            return $val;
        }
        return null;
    }



    /**
     * Return a group of entries from the registry
     *
     * for example if the prefixName = 'app.site'
     *
     * it would return all registry values with the key starting with `app.site.____`
     *
     * @param string $prefixName
     * @param boolean $truncateKey If true then the supplied $prefixName will be removed from the returned keys
     * @return array
     */
    public function getGroup($prefixName, $truncateKey = false)
    {
        $arr = array();
        foreach ($this as $k => $v) {
            if (preg_match('/^' . $prefixName . '\./', $k)) {
                if (!$truncateKey) {
                    $arr[$k] = $v;
                } else {
                    $arr[str_replace($prefixName.'.', '', $k)] = $v;
                }
            }
        }
        return $arr;
    }
}