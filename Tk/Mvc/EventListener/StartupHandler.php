<?php
namespace Tk\Mvc\EventListener;

use Bs\Registry;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tk\Config;
use Tk\Log;
use Tk\System;

class StartupHandler implements EventSubscriberInterface
{

    public static string $SCRIPT_START  =  '---------------------- Start ----------------------';
    public static string $SCRIPT_END    =  '--------------------- Shutdown --------------------';
    public static string $SCRIPT_LINE   =  '---------------------------------------------------';

    public static bool $SCRIPT_CALLED = false;


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

        $siteName = Registry::instance()?->getSiteName() ?? implode(' ', $_SERVER['argv']);
        if (System::getComposerJson()) {
            $siteName .= sprintf(' [%s]', System::getComposerJson()['name']);
        }
        if (System::getVersion()) {
            $siteName .= sprintf(' [v%s]', System::getVersion());
        }
        if (Config::instance()->isDev()) {
            $siteName .= ' {Dev}';
        }
        $this->info('- Project: ' . trim($siteName));

        if ($request) {
            $this->debug(sprintf('- Request: [%s][%s] %s%s%s%s',
                $request->getMethod(),
                http_response_code(),
                $request->getScheme() . '://' . $request->getHost(),
                $request->getBaseUrl(),
                $request->getPathInfo(),
                '?' . $_SERVER['QUERY_STRING'] ?? ''
            ));
            $this->debug('- Client IP: ' . $request->getClientIp());
            $this->debug('- Agent: ' . $request->headers->get('User-Agent') );
            if ($request->getSession()) {
                $this->debug(sprintf('- Session: %s [ID: %s]', $request->getSession()->getName(), $request->getSession()->getId()));
            }
        } else {
            $this->debug('- CLI: ' . implode(' ', $_SERVER['argv']));
            $this->debug('- Path: ' . Config::instance()->getBasePath());
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
            if (is_string($controller)) {
                $this->info('- Controller: ' . $controller);
            } else {
                $this->info('- Controller: {unknown}');
            }
        }
    }

    private function info(string $str)
    {
        Log::info($str);
    }

    private function debug(string $str)
    {
        Log::debug($str);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['onInit', 255], ['onRequest']],
            ConsoleEvents::COMMAND  => 'onCommand'
        ];
    }

}