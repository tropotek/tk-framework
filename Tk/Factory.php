<?php
namespace Tk;

use Composer\Autoload\ClassLoader;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Application;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\Routing\Generator\CompiledUrlGenerator;
use Symfony\Component\Routing\Loader\Configurator\CollectionConfigurator;
use Symfony\Component\Routing\Matcher\CompiledUrlMatcher;
use Symfony\Component\Routing\Matcher\Dumper\CompiledUrlMatcherDumper;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Tk\Cache\Adapter\Filesystem;
use Tk\Cache\Cache;
use Tk\Log\MonologLineFormatter;
use Tk\Mail\Gateway;
use Tk\Mvc\Bootstrap;
use Tk\Mvc\Dispatch;
use Tk\Mvc\FrontController;
use Tk\Traits\SingletonTrait;
use Tk\Traits\SystemTrait;
use Tk\Console\Command;
use Tt\Db;

class Factory extends Collection
{
    use SingletonTrait;
    use SystemTrait;

    protected function __construct() {
        parent::__construct();
    }

    public function getBootstrap(): Bootstrap
    {
        if (!$this->has('bootstrap')) {
            $bootstrap = new Bootstrap();
            $this->set('bootstrap', $bootstrap);
        }
        return $this->get('bootstrap');
    }

    public function getFrontController(): FrontController
    {
        if (!$this->has('frontController')) {
            $frontController = new FrontController();
            $this->set('frontController', $frontController);
        }
        return $this->get('frontController');
    }

    /**
     * @todo refactor this method to only return the Db object, as Pdo is deprecated
     */
//    public function getDb(string $name = 'default'): null|Pdo|Db
//    {
//        $key = 'db.'.trim($name);
//        if (!$this->has($key)) {
//            if ($name == 'mysql') {
//                return $this->getDbNew($name);
//            }
//            try {
//                $options = $this->getConfig()->getGroup($key, true);
//                if (count($options)) {
//                    if ($this->getConfig()->has('php.date.timezone') && !isset($options['timezone'])) {
//                        $options['timezone'] = $this->getConfig()->get('php.date.timezone');
//                    }
//                    $db = Pdo::instance($name, $options);
//                    $this->set($key, $db);
//                }
//            } catch (\Exception $e) {
//                error_log($e->getMessage());
//            }
//        }
//        return $this->get($key);
//    }

    public function getDb(string $name = 'mysql'): ?Db
    {
        $key = 'db.'.trim($name);
        if (!$this->has($key)) {
            try {
                $db = new Db($this->getConfig()->get($key));
                if ($this->getConfig()->has('php.date.timezone') && !isset($options['timezone'])) {
                    $db->setTimezone($this->getConfig()->get('php.date.timezone'));
                }
                $this->set($key, $db);
            } catch (\Exception $e) {
                error_log($e->getMessage());
            }
        }
        return $this->get($key);
    }

    public function getSession(): ?Session
    {
        if (!$this->has('session')) {
            try {
                $sessionDbHandler = null;
                if ($this->getDb() && $this->getConfig()->get('session.db_enable')) { //
                    $sessionDbHandler = new PdoSessionHandler(
                        $this->getDb()->getPdo(), $this->getConfig()->getGroup('session', true)
                    );
                    try {
                        $sessionDbHandler->createTable();
                    } catch (\Exception $e) { }
                }
                $sessionStorage = new NativeSessionStorage($this->getConfig()->getGroup('session', true), $sessionDbHandler);
                $session = new Session($sessionStorage);
                $session->setName('sn_' . md5($this->getConfig()->getBaseUrl()) ?? 'PHPSESSID');
                $this->set('session', $session);
            } catch (\PDOException $e) {
                die($e->getMessage());
            }
        }
        return $this->get('session');
    }

    public function getCookie(): Cookie
    {
        if (!$this->has('cookie')) {
            $cookie = new Cookie();
            $this->set('cookie', $cookie);
        }
        return $this->get('cookie');
    }

    public function getRequest(): Request
    {
        if (!$this->has('request')) {
            $request = Request::createFromGlobals();
            $this->set('request', $request);
        }
        return $this->get('request');
    }

    public function getRequestStack(): RequestStack
    {
        if (!$this->has('requestStack')) {
            $requestStack = new RequestStack();
            $this->set('requestStack', $requestStack);
        }
        return $this->get('requestStack');
    }

    public function getCompiledRoutes(): array
    {
        // Setup Routes and cache results.
        // Use `<Ctrl>+<Shift>+R` ro refresh the routing cache
        $systemCache = new Cache(new Filesystem($this->getSystem()->makePath($this->getConfig()->get('path.cache'))));
        if ((!$compiledRoutes = $systemCache->fetch('compiledRoutes')) || $this->getSystem()->isRefreshCacheRequest()) {
            ConfigLoader::create()->loadRoutes(new CollectionConfigurator($this->getRouteCollection(), 'routes'));
            $compiledRoutes = (new CompiledUrlMatcherDumper($this->getRouteCollection()))->getCompiledRoutes();
            // Storing the data in the cache for 60 minutes (comment this out if using callables in routes)
            $systemCache->store('compiledRoutes', $compiledRoutes, 60*60);
        }
        return $compiledRoutes;
    }

    public function getRouteCollection(): RouteCollection
    {
        if (!$this->has('routeCollection')) {
            $routeCollection = new RouteCollection();
            $this->set('routeCollection', $routeCollection);
        }
        return $this->get('routeCollection');
    }

    public function getRouteMatcher(): CompiledUrlMatcher
    {
        if (!$this->has('routeMatcher')) {
            $context = new RequestContext();
            $matcher = new CompiledUrlMatcher($this->getCompiledRoutes(), $context);
            $this->set('routeMatcher', $matcher);
            $this->set('routeContext', $context);
        }
        return $this->get('routeMatcher');
    }

    /**
     *  For generating URLs from routes
     *  $generator = new Routing\Generator\UrlGenerator($routes, $context);
     *  echo $generator->generate(
     *      'hello',
     *      ['name' => 'Fabien'],
     *      UrlGeneratorInterface::ABSOLUTE_URL
     *  );
     *   outputs something like http://example.com/somewhere/hello/Fabien
     */
    public function getRouteGenerator(): CompiledUrlGenerator
    {
        if (!$this->has('routeGenerator')) {
            $generator = new CompiledUrlGenerator($this->getCompiledRoutes(), $this->get('routeContext'));
            $this->set('routeGenerator', $generator);
        }
        return $this->get('routeGenerator');
    }

    public function getControllerResolver(): ControllerResolver
    {
        if (!$this->has('controllerResolver')) {
            $controllerResolver = new ControllerResolver();
            $this->set('controllerResolver', $controllerResolver);
        }
        return $this->get('controllerResolver');
    }

    public function getArgumentResolver(): ArgumentResolver
    {
        if (!$this->has('argumentResolver')) {
            $argumentResolver = new ArgumentResolver();
            $this->set('argumentResolver', $argumentResolver);
        }
        return $this->get('argumentResolver');
    }

    /**
     * @see https://symfony.com/doc/current/reference/events.html
     */
    public function getEventDispatcher(): ?EventDispatcher
    {
        if (!$this->has('eventDispatcher')) {
            $dispatcher = new EventDispatcher();
            $this->set('eventDispatcher', $dispatcher);
        }
        return $this->get('eventDispatcher');
    }

    public function initEventDispatcher(): ?EventDispatcher
    {
        if ($this->getEventDispatcher()) {
            new Dispatch($this->getEventDispatcher());
        }
        return $this->getEventDispatcher();
    }

    public function getLogger(): ?LoggerInterface
    {
        if (!$this->has('logger')) {
            $processors = [];
            if ($this->getConfig()->get('log.system.request')) {
                $requestLog = $this->getSystem()->makePath($this->getConfig()->get('log.system.request'));
                if (!is_file($requestLog) || is_writable($requestLog)) {
                    FileUtil::mkdir(dirname($requestLog));
                    file_put_contents($requestLog, ''); // Refresh log for this session
                }
                $processors[] = function ($record) use ($requestLog) {
                    if (isset($record['message'])) {
                        $str = $record['message'] . "\n";
                        if (is_writable($requestLog)) {
                            file_put_contents($requestLog, $str, FILE_APPEND | LOCK_EX);
                        }
                    }
                    return $record;
                };
            }

            $logger = new NullLogger();
            $enabled = true;
            if (!$this->getConfig()->get('log.ignore.noLog', false)) {

                // No log when using nolog in query param
                if ($this->getRequest()->query->has(Log::NO_LOG)) $enabled = false;

                // No logs for api calls (comment out when testing API`s)
                if (str_contains($this->getRequest()->getRequestUri(), '/api/')) $enabled = false;
            }

            if ($enabled) {
                $logger = new Logger('system', [], $processors);
                if (is_writable(ini_get('error_log'))) {
                    $handler = new StreamHandler(ini_get('error_log'), $this->getConfig()->get('log.logLevel', LogLevel::ERROR));
                    $formatter = new MonologLineFormatter();
                    $formatter->setColorsEnabled(true);
                    $formatter->setScriptTime($this->getConfig()->get('script.time'));
                    $handler->setFormatter($formatter);
                    $logger->pushHandler($handler);
                } else {
                    $handler = new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM);
                    $logger->pushHandler($handler);
                    error_log('Error accessing log file: ' . ini_get('error_log'));
                }
            }

            // Init \Tk\Log
            Log::instance($logger);

            $this->set('logger', $logger);
        }
        return $this->get('logger');
    }

    /**
     * Get the composer Class Loader object returned from the autoloader in the _prepend.php file
     */
    public function getClassLoader(): ?ClassLoader
    {
        return $this->get('classLoader');
    }

    /**
     * get the mail gateway to send emails
     */
    public function getMailGateway(): ?Gateway
    {
        if (!$this->has('mailGateway')) {
            $params = $this->getConfig()->all();
            if (!$this->getSystem()->isCli()) {
                $params['clientIp'] = $this->getRequest()->getClientIp();
                $params['hostname'] = $this->getRequest()->getHost();
                $params['referer'] = $this->getRequest()->server->get('HTTP_REFERER', '');
            }
            $gateway = new \Tk\Mail\Gateway($params);
            $gateway->setDispatcher($this->getEventDispatcher());
            $this->set('mailGateway', $gateway);
        }
        return $this->get('mailGateway');
    }

    public function getConsole(): Application
    {
        if (!$this->has('console')) {
            $app = new Application($this->getRegistry()->getSiteName(), $this->getSystem()->getVersion());
            $app->setDispatcher($this->getEventDispatcher());

            // Setup Global Console Commands
            $app->add(new Command\CleanData());
            $app->add(new Command\Upgrade());
            $app->add(new Command\Maintenance());
            $app->add(new Command\DbBackup());
            $app->add(new Command\Migrate());
            if ($this->getConfig()->isDebug()) {
                $app->add(new Command\Debug());
                $app->add(new Command\Mirror());
                $app->add(new Command\MakeModel());
                $app->add(new Command\MakeMapper());
                $app->add(new Command\MakeTable());
                $app->add(new Command\MakeForm());
                $app->add(new Command\MakeManager());
                $app->add(new Command\MakeEdit());
                $app->add(new Command\MakeAll());
            }

            $this->set('console', $app);
        }
        return $this->get('console');
    }
}