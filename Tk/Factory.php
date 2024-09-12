<?php
namespace Tk;

use Composer\Autoload\ClassLoader;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Application;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
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
use Tk\Logger\ErrorLog;
use Tk\Logger\RequestLog;
use Tk\Logger\StreamLog;
use Tk\Mail\Gateway;
use Tk\Mvc\Bootstrap;
use Tk\Mvc\Dispatch;
use Tk\Mvc\FrontController;
use Tk\Traits\SingletonTrait;
use Tk\Traits\SystemTrait;
use Tk\Console\Command;


/**
 *
 * @todo Time to refactor the Factory and try to remove a lot of unnecessary methods
 *       Suggest moving all the MVC stuff to the Bs lib
 */
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
     * @deprecated
     */
    final public function getDb(string $name = 'mysql'): void
    {
        throw new \Exception("Deprecated:: Use \Tk\Db static object ");
    }

    /**
     * setup DB based session object
     */
    public function initSession(): ?\Tk\Db\Session
    {
        if (!$this->has('session')) {
            session_name('sn_' . md5($this->getConfig()->getBaseUrl()));
            // init DB session if enabled
            if ($this->getConfig()->get('session.db_enable', false)) {
                \Tk\Db\Session::instance();
            }
            session_start();

            $_SESSION[\Tk\Db\Session::SID_IP]    = get_client_ip();
            $_SESSION[\Tk\Db\Session::SID_AGENT] = $_SERVER['HTTP_USER_AGENT'] ?? '';

            $this->set('session', null);
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
            $request->setSession(new Session());
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
        // todo: move to FrontController
        if (!$this->has('controllerResolver')) {
            $controllerResolver = new ControllerResolver();
            $this->set('controllerResolver', $controllerResolver);
        }
        return $this->get('controllerResolver');
    }

    public function getArgumentResolver(): ArgumentResolver
    {
        // todo: move to FrontController
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
        // todo: move to FrontController, keep method save in factory
        if (!$this->has('eventDispatcher')) {
            $dispatcher = new EventDispatcher();
            $this->set('eventDispatcher', $dispatcher);
        }
        return $this->get('eventDispatcher');
    }

    public function initEventDispatcher(): ?EventDispatcher
    {
        // todo: move to bootstrap
        if ($this->getEventDispatcher()) {
            new Dispatch($this->getEventDispatcher());
        }
        return $this->getEventDispatcher();
    }

    public function initLogger(): void
    {
        // Init \Tk\Log
        Log::setEnableNoLog($this->getConfig()->get('log.enableNoLog', true));
        $requestLog = $this->getSystem()->makePath($this->getConfig()->get('log.system.request'));
        Log::addHandler(new RequestLog($requestLog));
        if (is_writable(ini_get('error_log'))) {
            Log::addHandler(new StreamLog(ini_get('error_log'), $this->getConfig()->get('log.logLevel', LogLevel::DEBUG)));
        } else {
            Log::addHandler(new ErrorLog($this->getConfig()->get('log.logLevel', LogLevel::DEBUG)));
        }
    }

    /**
     * Get the composer Class Loader object returned from the autoloader in the _prepend.php file
     */
    public function getComposerLoader(): ?ClassLoader
    {
        return $this->get('composerLoader');
    }

    /**
     * get the mail gateway to send emails
     */
    public function getMailGateway(): ?Gateway
    {
        // move init to bootstrap keep method
        if (!$this->has('mailGateway')) {
            $params = $this->getConfig()->all();
            if (!$this->getSystem()->isCli()) {
                $params['clientIp'] = $this->getRequest()->getClientIp();
                $params['hostname'] = $this->getRequest()->getHost();
                $params['referer']  = $_SERVER['HTTP_REFERER'] ?? '';
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
            if ($this->getConfig()->isDev()) {
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