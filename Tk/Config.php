<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk;

require_once(dirname(__FILE__)."/Exception.php");
require_once(dirname(__FILE__)."/functions/php.php");
require_once(dirname(__FILE__)."/functions/string.php");

use Tk\Db\Exception;
/**
 * A Tk_Config/registry object to manage all system wide Tk_Config parameters
 *
 * Common Tk_Config Sections:
 *  o system: Any system runtime settings.
 *  o debug: Any debug \Tk\Config settings
 *  o database.[name]: Database settings, can have multiple, `default` is used by default.
 *
 *
 * @package Tk
 */
class Config extends Registry
{

    const RELEASE_DEV = 'dev';
    const RELEASE_TEST = 'test';
    const RELEASE_LIVE = 'live';

    /**
     * @var \Tk\Config
     */
    static $instance = null;



    /**
     * constructor
     *
     */
    public function __construct()
    {
        // Include base framework files
        //include_once (dirname(__FILE__) . '/Exception.php');
        //include_once (dirname(__FILE__) . '/functions/php.php');

        $this['system.isCli'] = false;
        if (substr(php_sapi_name(), 0, 3) == 'cli') {
            $this['system.isCli'] = true;
        }
        $this['system.tmpPath'] = ini_get('upload_tmp_dir');
        // Attach default listeners
        $this->attach(new DebugConfigObserver(), 'system.debugMode');
        $this->attach(new LogConfigObserver(), 'system.log.path');
        $this->attach(new LogLevelConfigObserver(), 'system.log.level');
        $this->attach(new TimezoneConfigObserver(), 'system.timezone');
        $this->attach(new TempConfigObserver(), 'system.tmpPath');


    }

    /**
     * Init the config
     *
     * @param string $sitePath
     * @param string $siteUrl (optional) Uses .htaccess RewriteBase parameter if it exists.
     */
    protected function init($sitePath, $siteUrl = '')
    {
        $this->set('system.sitePath', $sitePath);
        $this->set('system.siteUrl', $siteUrl);

        // This file cannot be overridden in src/config/ folder
        $this->parseConfigFile(dirname(dirname(__FILE__)).'/config/defaults.php');

        $this->parseConfigFile(dirname(dirname(__FILE__)).'/config/db.php');
        $this->parseConfigFile(dirname(dirname(__FILE__)).'/config/cookies.php');
        $this->parseConfigFile(dirname(dirname(__FILE__)).'/config/filesystem.php');
        $this->parseConfigFile(dirname(dirname(__FILE__)).'/config/mail.php');
        $this->parseConfigFile(dirname(dirname(__FILE__)).'/config/maintenance.php');
        $this->parseConfigFile(dirname(dirname(__FILE__)).'/config/session.php');
    }

    /**
     * Get an instance of this object
     *
     * @param string $sitePath
     * @param string $siteUrl
     * @return \Tk\Config
     */
    static function getInstance($sitePath = '', $siteUrl = '')
    {
        if (self::$instance == null) {
            $class = get_called_class();
            if (class_exists($class)) {
                self::$instance = new $class();
            } else {
                self::$instance = new self();
            }
            self::$instance->init($sitePath, $siteUrl);
        }
        return self::$instance;
    }


    /**
     * Parse a Tk\Config file either XML or INI
     *
     * @param string|\Tk\Path $file
     * @return $this
     * @throws \Tk\RuntimeException
     * @throws \Tk\IllegalArgumentException
     */
    public function parseConfigFile($file)
    {
        $file = Path::create($file);
        $locFile = '';
        if (is_file($this->getSrcPath() . '/config/' . $file->getBasename())) {
            $locFile = Path::create($this->getSrcPath() . '/config/' . $file->getBasename());
        }
        if (!$file->isReadable()) {
            return $this;
        }
        $ext = $file->getExtension();

        if ($ext == 'ini') {
            $array = $this->parseIniFile($file);
            $this->load($array);
            if ($locFile) {
                $array = $this->parseIniFile($locFile);
                $this->load($array);
            }
        } elseif ($ext == 'xml') {
            $array = $this->parseXmlFile($file);
            $this->load($array);
            if ($locFile) {
                $array = $this->parseXmlFile($locFile);
                $this->load($array);
            }
        } elseif ($ext == 'php') {
            $this->parsePhpFile($file);
            if ($locFile) {
                $this->parsePhpFile($locFile);
            }
        } else {
            throw new RuntimeException('Invalid config file: ' . $file);
        }
        return $this;
    }


    /**
     * Config objects cannot be saved in the session
     * or stored in a serialised way.
     * Initalisation should take place on every request.
     *
     * @return null
     */
    public function serialize()
    {
        return null;
    }

    /**
     * Config objects cannot be saved in the session
     * or stored in a serialised way.
     * Initalisation should take place on every request.
     *
     * @param array $data
     */
    public function unserialize($data)
    {
        $this->data = array();
    }

    /**
     * Take a class in the form of Tk_Some_Class
     * And convert it to a namespace class like \Tk\Some\Class
     * Alias for \Tk\Object::toNamespace()
     *
     * @param string $class
     * @return string
     */
    static function toNamespace($class)
    {
        return Object::toNamespace($class);
    }

    /**
     * Take a class in the form of \Tk\Some\Class
     * And convert it to a namespace class like Tk_Some_Class
     * Alias for \Tk\Object::fromNamespace()
     *
     * @param string $class
     * @return string
     */
    static function fromNamespace($class)
    {
        return Object::fromNamespace($class);
    }

    /**
     * Debug mode.
     * Debug mode is used to help with logging and
     * it also does the following:
     *
     *  o Makes all emails rout to the system.debugEmail account
     *    This ensures no test emails are sent to users unknowingly
     *  o Passwords are reset to 'password' when in debug mode.
     *    (NOTE: this could change in the future...)
     *
     * @return bool
     */
    public function isDebug()
    {
        return $this->get('system.debugMode');
    }

    /**
     * Release mode should be set according to what environment you
     * are running the system on.
     *   o RELEASE_DEV - Used when developing the application
     *   o RELEASE_TEST - Used when the application is being tested for release
     *   o RELEASE_LIVE - Use when deploying the application to production servers
     *
     * These settings allow us to control code that can only be run in these modes
     * It is imperitive that you do not set the wrong mode for the wrong server
     * as things may not run as expectedEasy access to the system.releaseMode Tk_Config option
     *
     * @return bool
     */
    public function isDev()
    {
        return ($this->get('system.releaseMode') == self::RELEASE_DEV);
    }

    /**
     * Release mode should be set according to what environment you
     * are running the system on.
     *   o RELEASE_DEV - Used when developing the application
     *   o RELEASE_TEST - Used when the application is being tested for release
     *   o RELEASE_LIVE - Use when deploying the application to production servers
     *
     * These settings allow us to control code that can only be run in these modes
     * It is imperitive that you do not set the wrong mode for the wrong server
     * as things may not run as expectedEasy access to the system.releaseMode Tk_Config option
     *
     * @return bool
     */
    public function isTest()
    {
        return ($this->get('system.releaseMode') == self::RELEASE_TEST);
    }

    /**
     * Release mode should be set according to what environment you
     * are running the system on.
     *   o TK_RELEASE_DEV - Used when developing the application
     *   o TK_RELEASE_TEST - Used when the application is being tested for release
     *   o TK_RELEASE_LIVE - Use when deploying the application to production servers
     *
     * These settings allow us to control code that can only be run in these modes
     * It is imperitive that you do not set the wrong mode for the wrong server
     * as things may not run as expectedEasy access to the system.releaseMode Tk_Config option
     *
     * @return bool
     */
    public function isLive()
    {
        return ($this->get('system.releaseMode') == self::RELEASE_LIVE);
    }



    /**
     * Helper function to get site path
     *
     * @return string
     */
    public function getSitePath()
    {
        return $this->get('system.sitePath');
    }

    /**
     * Helper function to get site url
     *
     * @return string
     */
    public function getSiteUrl()
    {
        if (strlen($this->get('system.siteUrl')) <= 1) {
            $this->set('system.siteUrl', '');
        }
        return $this->get('system.siteUrl');
    }

    /**
     * Helper function to get src path
     *
     * @return string
     */
    public function getSrcPath()
    {
        return $this->get('system.sitePath') . $this->get('system.srcPath');
    }

    /**
     * Helper function to get src url
     *
     * @return string
     */
    public function getSrcUrl()
    {
        return $this->get('system.siteUrl') . $this->get('system.srcUrl');
    }

    /**
     * Helper function to get vendor path
     *
     * @return string
     */
    public function getVendorPath()
    {
        return $this->get('system.sitePath') . $this->get('system.vendorPath');
    }

    /**
     * Helper function to get vendor url
     *
     * @return string
     */
    public function getVendorUrl()
    {
        return $this->get('system.siteUrl') . $this->get('system.vendorPath');
    }

    /**
     * Helper function to get Assets path
     *
     * @return string
     */
    public function getAssetsPath()
    {
        return $this->get('system.sitePath') . $this->get('system.assetsPath');
    }

    /**
     * Helper function to get Assets url
     *
     * @return string
     */
    public function getAssetsUrl()
    {
        return $this->get('system.siteUrl') . $this->get('system.assetsPath');
    }

    /**
     * Helper function to get lib path
     *
     * @return string
     */
    public function getLibPath()
    {
        return $this->get('system.sitePath') . $this->get('system.libPath');
    }

    /**
     * Helper function to get lib url
     *
     * @return string
     */
    public function getLibUrl()
    {
        return $this->get('system.siteUrl') . $this->get('system.libPath');
    }

    /**
     * Helper function to get data path
     *
     * @return string
     */
    public function getDataPath()
    {
        return $this->get('system.sitePath') . $this->get('system.dataPath');
    }

    /**
     * Helper function to get data url
     *
     * @return string
     */
    public function getDataUrl()
    {
        return $this->get('system.siteUrl') . $this->get('system.dataPath');
    }

    /**
     * Helper function to get cache path
     *
     * @return string
     */
    public function getCachePath()
    {
        return $this->get('system.sitePath') . $this->get('system.cachePath');
    }

    /**
     * Helper function to get cache url
     *
     * @return string
     */
    public function getCacheUrl()
    {
        return $this->get('system.siteUrl') . $this->get('system.cachePath');
    }

    /**
     * Helper function to get temp path
     *
     * @return string
     */
    public function getTmpPath()
    {
        return $this->get('system.tmpPath');
    }

    /**
     * Helper function to get temp url
     *
     * @deprecated
     */
    public function getTmpUrl()
    {
        throw new Exception('There should be no access to tmp path, copy to public path.');
    }

    /**
     * Helper function to get media path
     *
     * @return string
     */
    public function getMediaPath()
    {
        return $this->get('system.sitePath') . $this->get('system.mediaPath');
    }

    /**
     * Helper function to get media url
     *
     * @return string
     */
    public function getMediaUrl()
    {
        return $this->get('system.siteUrl') . $this->get('system.mediaPath');
    }





    /**
     * Helper function to get the site default email
     *
     * @return string
     */
    public function getSiteEmail()
    {
        return $this->get('system.site.email');
    }

    /**
     * Helper function to get the site default title
     *
     * @return string
     */
    public function getSiteTitle()
    {
        return $this->get('system.site.title');
    }

    /**
     * Is this a cli command
     *
     * @return bool
     */
    public function isCli()
    {
        return $this['system.isCli'];
    }


    /**
     * Get the config object
     * {Stop recursive error}
     *
     * @return \Tk\Config
     */
    public function getConfig()
    {
        return $this;
    }




    //-------------- FACTORY METHODS ------------------
    // List them in alphabetical order ....


    /**
     *
     *
     * @param string $content
     * @return \Tk\Mail\Message
     */
    public function createMailMessage($content = '')
    {
        $message = new \Tk\Mail\Message();
        if ($content) {
            $message->setBody($content);
        }
        return $message;
    }

    /**
     *
     *
     * @param string $template
     * @return \Tk\Mail\TplMessage
     */
    public function createMailTplMessage($template = '{content}')
    {
        $message = \Tk\Mail\TplMessage::create($template);
        return $message;
    }

    /**
     * Return the username
     * Returns the username if no \Usr\Db\User object exists
     *
     * @return \Usr\Db\User | string
     */
    public function getUser()
    {
        if ($this->getAuth()->getIdentity()) {
            if (!empty($this['system.auth.userClass'])) {
                $class = $this['system.auth.userClass'];
                return $class::getMapper()->findByUsername($this->getAuth()->getIdentity());
            }
            return $this->getAuth()->getIdentity();
        }
    }

    /**
     * get the users site relative home path
     *
     * @param mixed $user (optional) \Usr\Db\User obejct or string (username)
     * @return string
     */
    public function getHomePath($user = null)
    {
        $path = dirname($this->getHomeUrl($user));
        if (strlen($path) == 1) $path = '';
        return $path;
    }

    /**
     * get the users home page url
     *
     * @param mixed $user (optional) \Usr\Db\User obejct or string (username)
     * @return string
     */
    public function getHomeUrl($user = null)
    {
        if (!$user){
            $user = $this->getUser();
        }

        if ($user instanceof \Usr\Db\User) {
            if (method_exists($user, 'getHomeUrl')) {
                return $user->getHomeUrl();
            }
        }
        if ($user == \Tk\Auth\Auth::P_ADMIN) {
            return '/admin/index.html';
        }
        if ($user) {
            return '/user/index.html';
        }
        return '/index.html';
    }

    /**
     * Create an instance of the \Tk\Auth\Auth object
     *
     * @return \Tk\Auth\Auth
     */
    public function getAuth()
    {
        if (!$this->exists('res.auth')) {
            $obj = new Auth\Auth($this['system.auth.hashFunction']);
            $this->set('res.auth', $obj);
        }
        return $this->get('res.auth');
    }

    /**
     * Basic back url finder.
     *
     * @todo Use the session to store the referrer page that avoids page reloading issues.....
     * @return \Tk\Url
     */
    public function getBackUrl()
    {
        if ($this->exists('mod.back.url')) {
            return \Tk\Url::create($this->get('mod.back.url'));
        }
        if ($this->getRequest()->getReferer()) {
            return $this->getRequest()->getReferer();
        }
        return \Tk\Url::create('/index.html');
    }

    /**
     * Create an instance of the \Tk\Cache\Cache object
     * Uses the \Tk\Cache\Filesystem adapter
     *
     * @return \Tk\Cache\Cache
     */
    public function getCache()
    {
        if (!$this->exists('res.cache.filesystem')) {
            $obj = new Cache\Cache(new Cache\Adapter\Filesystem($this->getCachePath()));
            $this->set('res.cache.filesystem', $obj);
        }
        return $this->get('res.cache.filesystem');
    }

    /**
     * Create an instance of Tk PDO Database object
     * See the \PDO module docs
     *
     * @param string $configKey
     * @return \Tk\Db\Pdo
     */
    public function getDb($configKey = 'db.connect.default')
    {
        $arr = $this->get('res.db');
        if (!$arr) {
            $arr = array();
        }
        if (!isset($arr[$configKey])) {
            $params = $this->get($configKey);
            $dns = $params['type'] . ':dbname=' . $params['dbname'] . ';host=' . $params['host'];
            $obj = new Db\Pdo($dns, $params['user'], $params['pass']);
            // Set utf8 encoding....(security)
            $obj->exec("SET CHARACTER SET utf8");

            $arr[$configKey] = $obj;
            $this['res.db'] = $arr;
        }
        return $arr[$configKey];
    }

    /**
     * Get an instance of the URL Dispatcher
     *
     * @return \Tk\Dispatcher\Dispatcher
     */
    public function getDispatcher()
    {
        if (!$this->exists('res.dispatcher')) {
            $obj = new Dispatcher\Dispatcher(Request::getInstance()->getUri());
            $obj->attach(new Dispatcher\Ajax());
            $obj->attach(new Dispatcher\Module());
            $obj->attach($this->getDispatcherStatic());
            $this['res.dispatcher'] = $obj;
        }
        return $this->get('res.dispatcher');
    }

    /**
     * Create an instance of the URL Dispatcher
     *
     * @return \Tk\Dispatcher\StaticPath
     */
    public function getDispatcherStatic()
    {
        if (!$this->exists('res.dispatcherStaticPath')) {
            $obj = new Dispatcher\StaticPath();
            $this->set('res.dispatcherStaticPath', $obj);
        }
        return $this->get('res.dispatcherStaticPath');
    }

    /**
     * Create a site filesystem object.
     * Do not forget to call $fs->close() when your done...
     *
     * @return \Tk\Filesystem
     * @throws \Tk\Filesystem\Exception
     */
    public function getFilesystem()
    {
        if (!$this->exists('res.filesystem')) {
            // Detect OS type
            $ad = null;
            if (is_writable(__FILE__) && !$this->get('system.filesystem.ftp.enable')) {    // Assume suPHP here
                $ad = new Filesystem\Adapter\Local($this->getSitePath());
            } else {  // Require FTP adaptor
                if (!$this->get('system.ftp.host') || !$this->get('system.ftp.pass')) {
                    // TODO: this exception should be caught an the user directed to enter in their FTP details
                    throw new Filesystem\Exception('No FTP details available, cannot create FTP adapter.');
                }
                $ad = new Filesystem\Adapter_Ftp(
                    $this->get('system.ftp.host'), $this->get('system.ftp.user'), $this->get('system.ftp.pass'), $this->get('system.ftp.remotePath'), $this->get('system.ftp.port'), $this->get('system.ftp.retries'), $this->get('system.ftp.ftpPasv')
                );
                if (!$ad->connect()) {
                    throw new Filesystem\Exception('Cannot connect to FTP server.');
                }
            }
            $this['res.filesystem'] = new Filesystem\Filesystem($ad);
        }
        return $this->get('res.filesystem');
    }

    /**
     * Create an instance of the \Tk\Log\Log
     *
     * @return \Tk\Log\Log
     */
    public function getLog()
    {
        if (!$this->exists('res.log')) {
            $obj = Log\Log::getInstance();
            if ($this->getRequest()->get('_disableLog')) {
                $obj->setEnabled(false);
            }
            $obj->attach(new Log\Adapter\File($this->get('system.log.path'), $this->get('system.log.level')));
            $obj->attach(new Log\Adapter\Email($this->get('system.log.emailLevel')));
            $this['res.log'] = $obj;
        }
        return $this->get('res.log');
    }

    /**
     * Create/Start a session
     *
     * @param null $config
     * @return \Tk\Session
     */
    public function getSession($config = null)
    {
        if (!$this->exists('res.session')) {
            if ($config == null)
                $config = $this;
            $obj = Session::getInstance($config);
            $this['res.session'] = $obj;
        }
        return $this->get('res.session');
    }

    /**
     * Get the response object
     *
     * @return \Tk\Response
     */
    public function getResponse()
    {
        if (!$this->exists('res.response')) {
            $obj = Response::create();
            $this['res.response'] = $obj;
        }
        return $this->get('res.response');
    }

    /**
     * Get the request object
     *
     * @return \Tk\Request
     */
    public function getRequest()
    {
        if (!$this->exists('res.request')) {
            return Request::getInstance();
        }
    }

}



/* ************** SYSTEM CONFIG OBSERVERS *************** */

class DebugConfigObserver implements Observer
{

    public function update($obs)
    {
        if ($obs->isDebug()) {
            error_reporting(-1);
            error_reporting(E_ALL | E_STRICT);
            ini_set('display_errors', 'On');
        } else {
            error_reporting(0);
            ini_set('display_errors', 'Off');
        }
    }

}

class LogConfigObserver implements Observer
{

    public function update($obs)
    {
        ini_set('error_log', $obs['system.log.path']);
    }

}

class LogLevelConfigObserver implements Observer
{

    /**
     * @param \Tk\Config $obs
     */
    public function update($obs)
    {
        $val = $obs->get('system.log.level');
        if (!is_numeric($val)) {
            $val = eval('return ' . $val . ';');
        }
        $obs->enableNotify(false);
        $obs->set('system.log.level', (int)$val);
        $obs->enableNotify(true);
    }

}

class TimezoneConfigObserver implements Observer
{

    public function update($obs)
    {
        ini_set('date.timezone', $obs['system.timezone']);
        putenv('TZ=' . $obs['system.timezone']);
        date_default_timezone_set($obs['system.timezone']);
    }

}

class TempConfigObserver implements Observer
{

    public function update($obs)
    {
        if ($obs['system.tmpPath'] && !is_dir($obs['system.tmpPath'])) {
            mkdir($obs['system.tmpPath'], 0755, true);
        }
        ini_set('upload_tmp_dir', $obs['system.tmpPath']);
    }

}

