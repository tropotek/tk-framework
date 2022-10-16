<?php
namespace Tk\Mvc\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tk\Traits\SystemTrait;


/**
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class StartupHandler implements EventSubscriberInterface
{
    use SystemTrait;

    public static $SCRIPT_START  =  '---------------------- Start ----------------------';
    public static $SCRIPT_END    =  '--------------------- Shutdown --------------------';
    public static $SCRIPT_LINE   =  '---------------------------------------------------';

    public static $SCRIPT_CALLED = false;

    private LoggerInterface $logger;


    /**
     * @param LoggerInterface $logger
     */
    function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
     */
    public function onInit($event)
    {
        $this->init($event->getRequest());
    }

    /**
     * @param \Symfony\Component\Console\Event\ConsoleCommandEvent $event
     */
    public function onCommand($event)
    {
        $this->init($event->getRequest());
    }

    /**
     */
    private function init(Request $request)
    {
        self::$SCRIPT_CALLED = true;
        $this->out(self::$SCRIPT_START);

        $siteName = $this->getSystem()->getConfig()->get('system.site.name', '');
        if ($this->getSystem()->getComposer()) {
            $siteName .= sprintf(' [%s]', $this->getSystem()->getComposer()['name']);
        }
        if ($this->getSystem()->getVersion()) {
            $siteName .= sprintf(' [v%s]', $this->getSystem()->getVersion());
        }

        $this->out('- Project: ' . trim($siteName));
        $this->out('- Date: ' . date('Y-m-d H:i:s'));
        if (!$this->getSystem()->isCli()) {
            $this->out('- Host: ' . $request->getScheme() . '://' . $request->getHost());
            $this->out('- Base Path: ' . $request->getPathInfo());
            $this->out('- Base URL: ' . $request->getBaseUrl());
            $this->out('- Method: ' . $request->getMethod());
            $this->out('- Client IP: ' . $request->getClientIp());
            $this->out('- User Agent: ' . $request->headers->get('User-Agent') );
        } else {
            $this->out('- CLI: ' . implode(' ', $request->server->get('argv')));
        }
        if ($request->getSession()) {
            $this->out('- Session Name: ' . $request->getSession()->getName());
            $this->out('- Session ID: ' . $request->getSession()->getId());
        }
        $this->out('- Path: ' . $this->getConfig()->getBasePath());
        $this->out('- PHP: ' . \PHP_VERSION);
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
     */
    public function onRequest($event)
    {
        if ($event->getRequest()->attributes->has('_route')) {
            $this->out('- Controller: ' . $event->getRequest()->attributes->get('_controller'));
        }
        $this->out(self::$SCRIPT_LINE);
    }

    private function out(string $str)
    {
        $this->logger->info($str);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['onInit', 255], ['onRequest']],
           // \Symfony\Component\Console\ConsoleEvents::COMMAND  => 'onCommand'
        ];
    }

}