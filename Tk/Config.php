<?php
namespace Tk;

use Psr\Log\LoggerInterface;


/**
 * A Config class for handling the applications dependency values.
 *
 * It can be used as a standard array it extends the \Tk\Registry
 * Example usage:
 * <code>
 * $request = Request::createFromGlobals();
 * $cfg = \Tk\Config::cerate();     // required for first call then use getInstance()
 *
 * $cfg->setAppPath($sitePath);
 * $cfg->setRequest($request);
 * $cfg->setUrl($request->getBasePath());
 * $cfg->setDataPath($cfg->getSitePath().'/data');
 * $cfg->setCachePath($cfg->getDataPath().'/cache');
 * $cfg->setTempPath($cfg->getDataPath().'/temp');
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
 * @see http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 *
 * @todo Should we remove this object??, it causes us to rely on it at times, that also influences the code for bad object design
 *
 * @notes Will consider using a flat array and some sort of initialisation static method somewhere (A helper maybe???)
 *  -- See what other frameworks are doing for their config system, TIP: keep it simple....
 * This object has been used both as a place for global site settings and also a DI container
 * it may be time to separate these responsibilities and use some pattern that is more appropriate...???
 * I will leave it here for now but know that only the application level should be using this object nothing in the libs 
 * should be implementing this in the future...
 *
 * Wed, 17 Aug 2016: Still pondering this object and its implementation.
 *   It is a handy way to handle the config. Maybe we just clean it up
 *   and refer to it as a Registry Pattern and remove make instances at the App level ?????
 *
 */
class Config extends Collection
{

    /**
     * @var Config
     */
    public static $instance = null;

    /**
     * Reserved config keys
     * @var array
     */
    protected $reserved = array('data.url', 'data.path', 'vendor.url', 'vendor.path', 'src.url', 'src.path', 'cache.url',
        'cache.path', 'temp.url', 'temp.path');


    /**
     * Construct the config object and initiate default settings
     */
    public function __construct()
    {
        parent::__construct();
        $this->init();
    }

    /**
     * Create an instance of this object
     *
     * @return Config|static
     * @deprecated Do not think we need this now we have changed the path params
     */
    public static function create()
    {
        return self::getInstance();
    }

    /**
     * Get an instance of this object
     *
     * @return Config\static
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new static();
            // Load any site config files
            self::$instance->loadConfig();
        }
        return self::$instance;
    }

    /**
     * init the default params.
     */
    protected function init()
    {
        $config = $this;
        $config['script.time'] = microtime(true);

        // Setup isCli function in config.
        $config['cli'] = false;
        if (substr(php_sapi_name(), 0, 3) == 'cli') {
            $config['cli'] = true;
        }
        $config->setLogLevel('error');

        $config['file.mask']                = 0664;
        $config['dir.mask']                 = 0775;
        $config['system.data.path']         = '/data';
        $config['system.cache.path']        = '/data/cache';
        $config['system.temp.path']         = '/data/temp';
        $config['system.src.path']          = '/src';
        $config['system.vendor.path']       = '/vendor';
        $config['system.plugin.path']       = '/plugin';
        $config['system.assets.path']       = '/assets';
        $config['system.template.path']     = '/html';

        // setup site path and URL
        $config->initDefaultPaths();

        /**
         * This makes our life easier when dealing with paths. Everything is relative
         * to the application root now.
         */
        chdir($config->getSitePath());

        error_reporting(-1);
        ini_set('display_errors', 'On');

        $config->setDebug(false);
        $config->setLog(new \Psr\Log\NullLogger());
        $config->setTimezone('Australia/Victoria');

        if (ini_get('error_log')) {
            $config->setLogPath(ini_get('error_log'));
        } else {        // Default if none set in the php.ini
            $config->setLogPath('/var/log/apache2/error.log');
        }


        // Site information
        $config['system.info.project']      = 'untitledSite';
        $config['system.info.description']  = '';
        $config['system.info.version']      = '0.0';
        $config['system.info.licence']      = '';
        $config['system.info.released']     = '';
        $config['system.info.authors']      = '';
        $config['system.info.stability']    = '';


        if (is_file($config->getSitePath() . '/composer.json')) {
            $composer = json_decode(file_get_contents($config->getSitePath() . '/composer.json'));
            if (isset($composer->name))
                $config['system.info.project'] = $composer->name;
            if (isset($composer->description))
                $config['system.info.description'] = $composer->description;
            if (isset($composer->version))
                $config['system.info.version'] = $composer->version;
            if (isset($composer->license))
                $config['system.info.licence'] = $composer->license;
            if (isset($composer->time))
                $config['system.info.released'] = $composer->time;
            if (isset($composer->authors)) {
                $authStr = '';
                foreach ($composer->authors as $auth) {
                    if ($auth->homepage)
                        $authStr .= $auth->homepage . ', ';
                }
                $config['system.info.authors'] = trim($authStr, ', ');
            }
            if (isset($composer->{'minimum-stability'})) {
                $config['system.info.stability'] = $composer->{'minimum-stability'};
                $config['system.info.minimumStability'] = $composer->{'minimum-stability'};
            }
        }
    }

    /**
     * Load the site route config files
     */
    public function loadConfig()
    {
        $this->loadAppConfig();
    }

    /**
     * Load the site route config files
     */
    protected function loadAppConfig()
    {
        // Site Files
        if (is_file($this->getSrcPath() . '/config/application.php'))
            include($this->getSrcPath() . '/config/application.php');
        if (is_file($this->getSrcPath() . '/config/config.php'))
            include($this->getSrcPath() . '/config/config.php');

        // Could be handy for cli scripts using the \Tk\Uri
        $host = '';
        if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
        } else if (isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
        }
        if ($host) {
            if (is_writable($this->getCachePath())) { // Cache host
                file_put_contents($this->getCachePath().'/hostname', $host);
            }
        } else {    // Attempt to get the cached host
            if (is_readable($this->getDataPath().'/hostname')) {    // Can be set manually
                $host = file_get_contents($this->getCachePath() . '/hostname');
            } else if (is_readable($this->getCachePath().'/hostname')) {
                $host = file_get_contents($this->getCachePath() . '/hostname');
            }
        }
        $this->set('site.host', $host);
    }

    /**
     * Load the site route config files
     */
    public function loadRoutes()
    {
        $this->loadAppRoutes();
    }

    /**
     * Load the site route config files
     */
    public function loadAppRoutes()
    {
        // Site Files
        if (is_file($this->getSrcPath() . '/config/routes.php'))
            include($this->getSrcPath() . '/config/routes.php');
    }

    /**
     * This function tries to automatically determine the project path, url and host
     */
    protected function initDefaultPaths()
    {
        $sitePath = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
        $sitePath = rtrim($sitePath, '/');
        $this->set('site.path', $sitePath);

        $siteUrl = '/';
        // $_SERVER['SCRIPT_NAME']
        // $_SERVER['SCRIPT_FILENAME']
        if (isset($_SERVER['PHP_SELF']) && !isset($_SERVER['argv']) && !$_SERVER['PHP_SELF'][0] != '.') {
            $siteUrl = dirname($_SERVER['PHP_SELF']);
        } else {
            $htaccessFile = $this->getSitePath().'/.htaccess';
            if (@is_readable($htaccessFile)) {
                $htaccess = file_get_contents($htaccessFile);
                if ($htaccess && preg_match('/\s*RewriteBase (\/.*)\s+/i', $htaccess, $regs)) {
                    $siteUrl = $regs[1];
                }
            }
        }
        $siteUrl = rtrim($siteUrl, '/');
        $this->set('site.url', $siteUrl);
        \Tk\Uri::$BASE_URL_PATH = $siteUrl;

        $host = '';
        if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
        } else if (isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
        }
        $this->set('site.host', $host);
    }

    /**
     * @param array|Session $session
     * @return $this
     */
    public function setSession($session)
    {
        $this->set('session', $session);
        return $this;
    }

    /**
     * @return array|Session
     */
    public function getSession()
    {
        return $this->get('session');
    }

    /**
     * @param array|Request $request
     * @return $this
     */
    public function setRequest($request)
    {
        $this->set('request', $request);
        return $this;
    }

    /**
     * @return array|Request
     */
    public function getRequest()
    {
        return $this->get('request');
    }

    /**
     * @param array|Cookie $cookie
     * @return $this
     */
    public function setCookie($cookie)
    {
        $this->set('cookie', $cookie);
        return $this;
    }

    /**
     * @return array|Cookie
     */
    public function getCookie()
    {
        return $this->get('cookie');
    }


    /**
     * Set the system timezone:
     * EG: Australia/Victoria, America/Los_Angeles
     *
     * See DateTimeZone::listIdentifiers() to get an array of identifiers
     *
     * @param string $tz
     * @return Config
     */
    public function setTimezone($tz) {
        date_default_timezone_set($tz);
        $this->set('date.timezone', $tz);
        return $this;
    }

    /**
     * @return string
     */
    public function getTimezone()
    {
        return $this->get('date.timezone');
    }

    /**
     * Get the current script running time in seconds
     *
     * @return string
     */
    public static function scriptDuration()
    {
        return (string)(microtime(true) - self::getInstance()->get('script.time'));
    }

    /**
     * @param boolean $truncateKeys If true then the supplied $prefixName will be removed from the returned keys
     * @return array
     */
    public function getSystemInfo($truncateKeys = false)
    {
        return $this->getGroup('system.info', $truncateKeys);
    }

    /**
     * Get the octal permission mask for files
     * 
     * @return int
     */
    public function getFileMask()
    {
        return $this->get('file.mask');
    }

    /**
     * Get the octal permission mask for directories
     * 
     * @return int
     */
    public function getDirMask()
    {
        return $this->get('dir.mask');
    }

    /**
     * @return int
     */
    public function getScriptTime()
    {
        return $this->get('script.time');
    }

    /**
     * @return LoggerInterface
     */
    public function getLog()
    {
        return $this->get('log');
    }

    /**
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @return $this
     */
    public function setLog($logger)
    {
        $this->set('log', $logger);
        return $this;
    }

    /**
     * TODO: should we use this to setup the php system log levels???
     *
     * @param $level
     * @return $this
     */
    public function setLogLevel($level)
    {
        return $this->set('log.level', $level);
    }

    /**
     * @return mixed
     */
    public function getLogLevel()
    {
        return $this->get('log.level');
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setLogPath($path)
    {
        ini_set('error_log', $path);
        return $this->set('log.path', $path);
    }

    /**
     * @return string
     */
    public function getLogPath()
    {
        return $this->get('log.path');
    }


    /**
     * Get the site host domain name
     * @return string
     */
    public function getSiteHost()
    {
        return $this->get('site.host');
    }

    /**
     * Get the URL path to the root of the project
     * @return string
     */
    public function getSiteUrl()
    {
        return rtrim($this->get('site.url'), '/');
    }

    /**
     * Get the filesystem path to the root of the project
     * @return string
     */
    public function getSitePath()
    {
        return rtrim($this->get('site.path'), '/');
    }

    /**
     * @return string
     */
    public function getDataUrl()
    {
        return $this->getSiteUrl() . rtrim($this->get('system.data.path'), '/');
    }

    /**
     * @return string
     */
    public function getDataPath()
    {
        return $this->getSitePath() . rtrim($this->get('system.data.path'), '/');
    }

    /**
     * @return string
     */
    public function getTemplateUrl()
    {
        return $this->getSiteUrl() . rtrim($this->get('system.template.path'), '/');
    }

    /**
     * @return string
     */
    public function getTemplatePath()
    {
        return $this->getSitePath() . rtrim($this->get('system.template.path'), '/');
    }

    /**
     * @return string
     */
    public function getSrcUrl()
    {
        return $this->getSiteUrl() . rtrim($this->get('system.src.path'), '/');
    }

    /**
     * @return string
     */
    public function getSrcPath()
    {
        return $this->getSitePath() . rtrim($this->get('system.src.path'), '/');
    }

    /**
     * @return string
     */
    public function getCacheUrl()
    {
        return $this->getSiteUrl() . rtrim($this->get('system.cache.path'), '/');
    }

    /**
     * @return string
     */
    public function getCachePath()
    {
        $path = $this->getSitePath() . rtrim($this->get('system.cache.path'), '/');
        if (!is_dir($path)) {
            if (!mkdir($path, $this->getDirMask(), true)) {
                die('Error: Cannot create Cache directory.');
            }
        }
        return $path;
    }

    /**
     * @return string
     */
    public function getVendorUrl()
    {
        return $this->getSiteUrl() . rtrim($this->get('system.vendor.path'), '/');
    }

    /**
     * @return string
     */
    public function getVendorPath()
    {
        return $this->getSitePath() . rtrim($this->get('system.vendor.path'), '/');
    }

    /**
     * @return string
     */
    public function getPluginUrl()
    {
        return $this->getSiteUrl() . rtrim($this->get('system.plugin.path'), '/');
    }

    /**
     * @return string
     */
    public function getPluginPath()
    {
        return $this->getSitePath() . rtrim($this->get('system.plugin.path'), '/');
    }

    /**
     * @return string
     */
    public function getAssetsUrl()
    {
        return $this->getSiteUrl() . rtrim($this->get('system.assets.path'), '/');
    }

    /**
     * @return string
     */
    public function getAssetsPath()
    {
        return $this->getSitePath() . rtrim($this->get('system.assets.path'), '/');
    }

    /**
     * @return string
     */
    public function getTempUrl()
    {
        return $this->getSiteUrl() . rtrim($this->get('system.temp.path'), '/');
    }

    /**
     * @return string
     */
    public function getTempPath()
    {
        
        $path = $this->getSitePath() . rtrim($this->get('system.temp.path'), '/');
        if (!is_dir($path)) {
            if(!@mkdir($path, $this->getDirMask(), true)) {
                dir('Error: Cannot create tmp directory.');
            }
        }
        return $path;
    }



    /**
     * Is the application in debug mode
     *
     * @return boolean
     */
    public function isDebug()
    {
        return $this->get('debug');
    }

    /**
     * @param boolean $b
     * @return $this
     */
    public function setDebug($b)
    {
        return $this->set('debug', $b);
    }

    /**
     * Is the environment a Command line interface (CLI)
     *
     * @return boolean
     */
    public function isCli()
    {
        return $this->get('cli');
    }

    /**
     * Get the database
     *
     * @return \Tk\Db\Pdo
     */
    public function getDb()
    {
        return $this->get('db');
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
     * @param array $argv
     * @return mixed|null
     * @throws Exception
     */
    public function __call($func, $argv)
    {
        $key = preg_replace('/[A-Z]/', '.$0', $func);
        $key = strtolower($key);

        $pos = strpos($key, '.');
        $type = substr($key, 0, $pos);
        $key = substr($key, $pos+1);

        if (in_array($key, $this->reserved))
            throw new \Tk\Exception('Reserved keywords cannot be used: ' . $key);

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
     * @param boolean $truncateKeys If true then the supplied $prefixName will be removed from the returned keys
     * @return array
     */
    public function getGroup($prefixName, $truncateKeys = false)
    {
        $arr = array();
        foreach ($this as $k => $v) {
            if (preg_match('/^' . $prefixName . '\./', $k)) {
                if (!$truncateKeys) {
                    $arr[$k] = $v;
                } else {
                    $arr[str_replace($prefixName.'.', '', $k)] = $v;
                }
            }
        }
        return $arr;
    }

    /**
     * @param string $path
     * @return Config
     * @deprecated Use setLogPath()
     */
    public function setSystemLogPath($path)
    {
        return $this->setLogPath($path);
    }

    /**
     * @return string
     * @deprecated Use getLogLevel()
     */
    public function getSystemLogLevel()
    {
        return $this->getLogLevel();
    }

    /**
     * @param string $l
     * @return Config
     * @deprecated Use setLogLevel()
     */
    public function setSystemLogLevel($l)
    {
        return $this->setLogLevel($l);
    }

    /**
     * @param string $tz
     * @deprecated Use setTimezone()
     * @return Config
     */
    public function setDateTimezone($tz) {
        return $this->setTimezone($tz);
    }

    /**
     * @return string
     * @deprecated Use getTimezone()
     */
    public function getDateTimezone()
    {
        return $this->getTimezone();
    }

    /**
     * Create a random password
     *
     * @param int $length
     * @return string
     */
    public static function createPassword($length = 8)
    {
        $chars = '234567890abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ';
        $i = 0;
        $password = '';
        while ($i <= $length) {
            $password .= $chars[mt_rand(0, strlen($chars) - 1)];
            $i++;
        }
        return $password;
    }

    /**
     * Return the back URI if available, otherwise it will return the home URI
     *
     * @return Uri
     */
    public function getBackUrl()
    {
        if ($this->getRequest()->getReferer()) {
            return $this->getRequest()->getReferer();
        }
        return Uri::create('/index.html');
    }


    /**
     * getEmailGateway
     *
     * @return \Tk\Mail\Gateway
     */
    public function getEmailGateway()
    {
        if (!$this->get('email.gateway')) {
            $gateway = new \Tk\Mail\Gateway($this);
            $this->set('email.gateway', $gateway);
        }
        return $this->get('email.gateway');
    }

    /**
     * @param string $xtplFile The mail template filename as found in the /html/xtpl/mail folder
     * @return \Tk\Mail\CurlyMessage
     */
    public function createMessage($xtplFile = 'default')
    {
        $config = self::getInstance();
        $request = $config->getRequest();

        $template = '{content}';
        $xtplFile = str_replace(array('./', '../'), '', strip_tags(trim($xtplFile)));
        $xtplFile = $config->get('template.xtpl.path') . '/mail/' . $xtplFile . $config->get('template.xtpl.ext');
        if (is_file($xtplFile)) {
            $template = file_get_contents($xtplFile);
            if (!$template) {
                \Tk\Alert::addWarning('Template file not found, using default template: ' . $xtplFile);
                $template = '{content}';
            }
        }

        $message = \Tk\Mail\CurlyMessage::create($template);
        $message->setFrom($config->get('site.email'));

        if ($request->getUri())
            $message->set('_uri', \Tk\Uri::create('')->toString());
        if ($request->getReferer())
            $message->set('_referer', $request->getReferer()->toString());
        if ($request->getIp())
            $message->set('_ip', $request->getIp());
        if ($request->getUserAgent())
            $message->set('_user_agent', $request->getUserAgent());

        return $message;
    }
}