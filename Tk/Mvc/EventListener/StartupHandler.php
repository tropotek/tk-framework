<?php
namespace Tk\Mvc\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tk\Traits\SystemTrait;

class StartupHandler implements EventSubscriberInterface
{
    use SystemTrait;

    public static string $SCRIPT_START  =  '---------------------- Start ----------------------';
    public static string $SCRIPT_END    =  '--------------------- Shutdown --------------------';
    public static string $SCRIPT_LINE   =  '---------------------------------------------------';

    public static bool $SCRIPT_CALLED = false;

    private LoggerInterface $logger;


    function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onInit(RequestEvent $event)
    {
        $this->init($event->getRequest());
    }

    public function onCommand(ConsoleCommandEvent $event)
    {
        $this->init();
    }

    private function init(?Request $request = null)
    {
        self::$SCRIPT_CALLED = true;
        $this->info(self::$SCRIPT_START);

        $siteName = $this->getSystem()->getRegistry()?->getSiteName() ?? implode(' ', $_SERVER['argv']);
        if ($this->getSystem()->getComposerJson()) {
            $siteName .= sprintf(' [%s]', $this->getSystem()->getComposerJson()['name']);
        }
        if ($this->getSystem()->getVersion()) {
            $siteName .= sprintf(' [v%s]', $this->getSystem()->getVersion());
        }
        if ($this->getConfig()->isDebug()) {
            $siteName .= ' {DEBUG}';
        }
        $this->info('- Project: ' . trim($siteName));

        if ($request) {
            $this->debug(sprintf('- Request: [%s][%s] %s%s%s',
                $request->getMethod(),
                http_response_code(),
                $request->getScheme() . '://' . $request->getHost(),
                $request->getBaseUrl(),
                $request->getPathInfo()
            ));
            $this->debug('- Client IP: ' . $request->getClientIp());
            $this->debug('- Agent: ' . $request->headers->get('User-Agent') );
            if ($request->getSession()) {
                $this->debug(sprintf('- Session: %s [ID: %s]', $request->getSession()->getName(), $request->getSession()->getId()));
            }
        } else {
            $this->debug('- CLI: ' . implode(' ', $_SERVER['argv']));
            $this->debug('- Path: ' . $this->getConfig()->getBasePath());
        }
        $this->debug('- PHP: ' . \PHP_VERSION);
        $this->info(self::$SCRIPT_LINE);
    }

    public function onRequest(RequestEvent $event)
    {
        if ($event->getRequest()->attributes->has('_route')) {
            $controller = $event->getRequest()->attributes->get('_controller');
            if (is_array($controller)) {
                $controller = implode('::', $controller);
            }
            $this->info('- Controller: ' . $controller);
        }
    }

    private function info(string $str)
    {
        $this->logger->info($str);
    }

    private function debug(string $str)
    {
        $this->logger->debug($str);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['onInit', 255], ['onRequest']],
            ConsoleEvents::COMMAND  => 'onCommand'
        ];
    }

}