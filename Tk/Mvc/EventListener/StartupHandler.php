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
        $this->out(self::$SCRIPT_START);

        $siteName = $this->getSystem()->getRegistry()->getSiteName();
        if ($this->getSystem()->getComposerJson()) {
            $siteName .= sprintf(' [%s]', $this->getSystem()->getComposerJson()['name']);
        }
        if ($this->getSystem()->getVersion()) {
            $siteName .= sprintf(' [v%s]', $this->getSystem()->getVersion());
        }

        $this->out('- Project: ' . trim($siteName));
        $this->out('- Date: ' . date('Y-m-d H:i:s'));
        if ($request) {
            $this->out('- Host: ' . $request->getScheme() . '://' . $request->getHost());
            $this->out('- Base Path: ' . $request->getPathInfo());
            $this->out('- Base URL: ' . $request->getBaseUrl());
            $this->out('- Method: ' . $request->getMethod());
            $this->out('- Client IP: ' . $request->getClientIp());
            $this->out('- User Agent: ' . $request->headers->get('User-Agent') );
            if ($request->getSession()) {
                $this->out('- Session Name: ' . $request->getSession()->getName());
                $this->out('- Session ID: ' . $request->getSession()->getId());
            }
        } else {
            $this->out('- CLI: ' . implode(' ', $_SERVER['argv']));
        }
        $this->out('- Path: ' . $this->getConfig()->getBasePath());
        $this->out('- PHP: ' . \PHP_VERSION);
        $this->out(self::$SCRIPT_LINE);
    }

    public function onRequest(RequestEvent $event)
    {
        if ($event->getRequest()->attributes->has('_route')) {
            $controller = $event->getRequest()->attributes->get('_controller');
            if (is_array($controller)) {
                $controller = implode('::', $controller);
            }
            $this->out('- Controller: ' . $controller);
        }
    }

    private function out(string $str)
    {
        $this->logger->info($str);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['onInit', 255], ['onRequest']],
            ConsoleEvents::COMMAND  => 'onCommand'
        ];
    }

}