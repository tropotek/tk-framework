<?php
namespace Tk;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;


/**
 * A Config class for handling the applications dependency values.
 *
 * It can be used as a standard array it extends the \Tk\Registry
 * Example usage:
 * <code>
 * $request = Request::createFromGlobals();
 * $cfg = \Tk\Config::getInstance();
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
 * @link http://www.tropotek.com/
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
    protected $reserved = array('data.url', 'data.path', 'vendor.url', 'vendor.path', 'src.url', 'src.path', 'cache.url', 'cache.path', 'temp.url', 'temp.path');


    /**
     * Construct the config object and initiate default settings
     *
     * @param string $siteUrl
     * @param string $sitePath
     */
    public function __construct($sitePath = '', $siteUrl = '')
    {
        parent::__construct();
        $this->init($sitePath, $siteUrl);
    }

    /**
     * Get an instance of this object
     *
     * @param string $siteUrl Only required on first call to init the config paths
     * @param string $sitePath Only required on first call to init the config paths
     * @return Config
     */
    public static function getInstance($sitePath = '', $siteUrl = '')
    {
        if (static::$instance == null) {
            static::$instance = new static($sitePath, $siteUrl);
        }
        return static::$instance;
    }

    /**
     * init the default params.
     *
     * @param string $sitePath
     * @param string $siteUrl
     */
    protected function init($sitePath = '', $siteUrl = '')
    {
        $config = $this;
        $config['script.time'] = microtime(true);

        // Setup isCli function in config.
        $config['cli'] = false;
        if (substr(php_sapi_name(), 0, 3) == 'cli') {
            $config['cli'] = true;
        }

        // setup site path and URL
        list($config['site.path'], $config['site.url']) = $this->getDefaultPaths($sitePath, $siteUrl);


        $config['debug'] = false;
        $config['log'] = new NullLogger();
        $config['system.log.path'] = ini_get('error_log');
        $config['system.log.level'] = 'error';
        $config['date.timezone'] = 'Australia/Victoria';
        $config['file.mask'] = 0664;
        $config['dir.mask'] = 0775;

        $config['system.data.path'] =     '/data';
        $config['system.cache.path'] =    '/data/cache';
        $config['system.temp.path'] =     '/data/temp';
        $config['system.src.path'] =      '/src';
        $config['system.vendor.path'] =   '/vendor';
        $config['system.plugin.path'] =   '/plugin';
        $config['system.assets.path'] =   '/assets';
        $config['system.template.path'] = '/html';


        // Site information
        $config['system.info.project'] = 'Untitled Site';
        $config['system.info.description'] = '';
        $config['system.info.version'] = '0.0';
        $config['system.info.licence'] = '';
        $config['system.info.released'] = '';
        $config['system.info.authors'] = '';
        $config['system.info.stability'] = '';

        if (is_file($this->getSitePath() . '/composer.json')) {
            $composer = json_decode(file_get_contents($this->getSitePath() . '/composer.json'));
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
     * @return string
     */
    public function getSiteUrl()
    {
        return $this->get('site.url');
    }

    /**
     * @return string
     */
    public function getSitePath()
    {
        return $this->get('site.path');
    }

    /**
     * @return string
     */
    public function getDataUrl()
    {
        return $this->getSiteUrl() . $this->get('system.data.path');
    }

    /**
     * @return string
     */
    public function getDataPath()
    {
        return $this->getSitePath() . $this->get('system.data.path');
    }

    /**
     * @return string
     */
    public function getTemplateUrl()
    {
        return $this->getSiteUrl() . $this->get('system.template.path');
    }

    /**
     * @return string
     */
    public function getTemplatePath()
    {
        return $this->getSitePath() . $this->get('system.template.path');
    }

    /**
     * @return string
     */
    public function getSrcUrl()
    {
        return $this->getSiteUrl() . $this->get('system.src.path');
    }

    /**
     * @return string
     */
    public function getSrcPath()
    {
        return $this->getSitePath() . $this->get('system.src.path');
    }

    /**
     * @return string
     */
    public function getCacheUrl()
    {
        return $this->getSiteUrl() . $this->get('system.cache.path');
    }

    /**
     * @return string
     */
    public function getCachePath()
    {
        $path = $this->getSitePath() . $this->get('system.cache.path');
        if (!is_dir($path)) {
            mkdir($path, $this->getDirMask(), true);
        }
        return $path;
    }

    /**
     * @return string
     */
    public function getVendorUrl()
    {
        return $this->getSiteUrl() . $this->get('system.vendor.path');
    }

    /**
     * @return string
     */
    public function getVendorPath()
    {
        return $this->getSitePath() . $this->get('system.vendor.path');
    }

    /**
     * @return string
     */
    public function getPluginUrl()
    {
        return $this->getSiteUrl() . $this->get('system.plugin.path');
    }

    /**
     * @return string
     */
    public function getPluginPath()
    {
        return $this->getSitePath() . $this->get('system.plugin.path');
    }

    /**
     * @return string
     */
    public function getAssetsUrl()
    {
        return $this->getSiteUrl() . $this->get('system.assets.path');
    }

    /**
     * @return string
     */
    public function getAssetsPath()
    {
        return $this->getSitePath() . $this->get('system.assets.path');
    }

    /**
     * @return string
     */
    public function getTempUrl()
    {
        return $this->getSiteUrl() . $this->get('system.temp.path');
    }

    /**
     * @return string
     */
    public function getTempPath()
    {
        
        $path = $this->getSitePath() . $this->get('system.temp.path');
        if (!is_dir($path)) {
            if(!@mkdir($path, $this->getDirMask(), true)) {
                throw new \Tk\Exception('Please change the permissions on your sites data path.');
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
     * This function tries to automatically determine the app path and url
     * @param string $sitePath
     * @param string $siteUrl
     * @return array
     */
    protected function getDefaultPaths($sitePath = '', $siteUrl = '')
    {
        // Determine the default path
        if (!$sitePath) {
            $sitePath = rtrim( dirname(dirname(dirname(dirname(dirname(__FILE__))))) , '/');
        }
        // Determine the default base url
        if (!$siteUrl && isset($_SERVER['PHP_SELF'])) {
            $siteUrl = dirname($_SERVER['PHP_SELF']);
        }
        $siteUrl = rtrim($siteUrl, '/');
        return array($sitePath, $siteUrl);
    }
}