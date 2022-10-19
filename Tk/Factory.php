<?php
namespace Tk;

use Composer\Autoload\ClassLoader;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LogLevel;
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
use Symfony\Component\Routing\Matcher\CompiledUrlMatcher;
use Symfony\Component\Routing\Matcher\Dumper\CompiledUrlMatcherDumper;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Tk\Auth\Adapter\AdapterInterface;
use Tk\Auth\Auth;
use Tk\Auth\FactoryInterface;
use Tk\Cache\Adapter\Filesystem;
use Tk\Cache\Cache;
use Tk\Db\Pdo;
use Tk\Log\MonologLineFormatter;
use Tk\Mvc\Bootstrap;
use Tk\Mvc\Dispatch;
use Tk\Mvc\FrontController;
use Tk\Traits\SingletonTrait;
use Tk\Traits\SystemTrait;
use \Tk\Console\Command;

/**
 * @author Tropotek <http://www.tropotek.com/>
 */
class Factory extends Collection implements FactoryInterface
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

    public function getDb(string $name = 'default'): ?Pdo
    {
        $key = 'db.'.trim($name);
        if (!$this->has($key)) {
            try {
                $options = $this->getConfig()->getGroup($key, true);
                if (count($options)) {
                    if ($this->getConfig()->has('php.date.timezone') && !isset($options['timezone'])) {
                        $options['timezone'] = $this->getConfig()->get('php.date.timezone');
                    }
                    $db = Pdo::instance($name, $options);
                    $this->set($key, $db);
                }
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
                        $this->getDb(), $this->getConfig()->getGroup('session', true)
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
                error_log($e->getMessage());
            }
        }
        return $this->get('session');
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
        vd();
        $systemCache = new Cache(new Filesystem($this->getSystem()->makePath($this->getConfig()->get('path.cache') . '/system')));
        if ((!$compiledRoutes = $systemCache->fetch('compiledRoutes')) || $this->getSystem()->isRefreshCacheRequest()) {
            include($this->getSystem()->makePath($this->getConfig()->get('path.routes')));
            $compiledRoutes = (new CompiledUrlMatcherDumper($this->getRouteCollection()))->getCompiledRoutes();
            // Storing the data in the cache for 60 minutes
            vd($compiledRoutes);
            $systemCache->store('compiledRoutes', $compiledRoutes, 60*60);
        }
        return $compiledRoutes;
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

    public function getRouteCollection(): RouteCollection
    {
        if (!$this->has('routeCollection')) {
            $routeCollection = new RouteCollection();
            $this->set('routeCollection', $routeCollection);
        }
        return $this->get('routeCollection');
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

    public function getLogger(): ?Logger
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
                    if (isset($record['message']) && !$this->getRequest()->query->has(Log::NO_LOG)) {
                        $str = $record['message'] . "\n";
                        if (is_writable($requestLog)) {
                            file_put_contents($requestLog, $str, FILE_APPEND | LOCK_EX);
                        }
                    }
                    return $record;
                };
            }

            $logger = new Logger('system', array(), $processors);

            if (is_writable(ini_get('error_log'))) {
                $handler = new StreamHandler(ini_get('error_log'), $this->getConfig()->get('log.logLevel', LogLevel::ERROR));
                $formatter = new MonologLineFormatter();
                $formatter->setColorsEnabled(true);
                $formatter->setScriptTime($this->getConfig()->get('script.time'));
                $handler->setFormatter($formatter);
                $logger->pushHandler($handler);
            } else {
                error_log('Error accessing log file: ' . ini_get('error_log'));
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
    public function getComposerClassLoader(): ?ClassLoader
    {
        return $this->get('composerClassLoader');
    }

    public function getAuthController(): Auth
    {
        if (!$this->has('authController')) {
            $auth = new Auth(new \Tk\Auth\Storage\SessionStorage($this->getSession()));
            $this->set('authController', $auth);
        }
        return $this->get('authController');
    }

    /**
     * This is the default Authentication adapter
     * Override this method in your own site's Factory object
     */
    public function getAuthAdapter(): AdapterInterface
    {
        if (!$this->has('authAdapter')) {
            $adapter = new \Tk\Auth\Adapter\Config('admin', hash('md5', 'password'));
            $this->set('authAdapter', $adapter);
        }
        return $this->get('authAdapter');
    }

    /**
     * Return a User object or record that is located from the Auth's getIdentity() method
     * Override this method in your own site's Factory object
     * @return null|mixed Null if no user logged in
     */
    public function getAuthUser()
    {
        if (!$this->has('authUser')) {
            if ($this->getAuthController()->hasIdentity()) {
                $user = $this->getAuthController()->getIdentity();
                $this->set('authUser', $user);
            }
        }
        return $this->get('authUser');
    }

    /**
     * @return Application
     */
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
//                $app->add(new \Bs\Console\MakeModel());
//                $app->add(new \Bs\Console\MakeTable());
//                $app->add(new \Bs\Console\MakeManager());
//                $app->add(new \Bs\Console\MakeForm());
//                $app->add(new \Bs\Console\MakeEdit());
//                $app->add(new \Bs\Console\MakeAll());
            }

            $this->set('console', $app);
        }
        return $this->get('console');
    }
}