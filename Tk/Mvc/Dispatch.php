<?php
namespace Tk\Mvc;

use Bs\Factory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tk\Config;
use Tk\Mvc\EventListener\ShutdownHandler;
use Tk\Mvc\EventListener\StartupHandler;
use Tk\System;

/**
 * This object sets up the EventDispatcher and
 * attaches all the listeners required for your application.
 *
 * Subclass this object in your App (to setup a Tk framework) and then override the Factory method
 * Factory::initDispatcher()
 */
class Dispatch
{

    protected ?EventDispatcherInterface $dispatcher = null;


    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        $this->init();
    }

    private function init()
    {
        $this->commonInit();
        if (System::isCli()) {
            $this->cliInit();
        } else {
            $this->httpInit();
        }
    }

    /**
     * Any Common listeners that are used in both HTTPS or CLI requests
     */
    protected function commonInit()
    {
        if (Config::instance()->isDev()) {
            $this->getDispatcher()->addSubscriber(new StartupHandler());
            $this->getDispatcher()->addSubscriber(new ShutdownHandler(Config::instance()->get('script.start.time')));
        }
    }

    /**
     * Called this when executing http requests
     */
    protected function httpInit()
    {
        $this->getDispatcher()->addSubscriber(new \Symfony\Component\HttpKernel\EventListener\RouterListener(
            Factory::instance()->getRouteMatcher(),
            Factory::instance()->getRequestStack()
        ));

        $this->getDispatcher()->addSubscriber(new \Tk\Mvc\EventListener\LogExceptionListener(
            Config::instance()->isDebug()
        ));

        $this->getDispatcher()->addSubscriber(new \Tk\Mvc\EventListener\ViewHandler());
        $this->getDispatcher()->addSubscriber(new \Symfony\Component\HttpKernel\EventListener\ResponseListener('UTF-8'));
        $this->getDispatcher()->addSubscriber(new \Tk\Mvc\EventListener\ContentLength());

    }

    /**
     * Called this when executing Console/CLI requests
     */
    protected function cliInit()
    {

    }

    /**
     * @return  EventDispatcherInterface
     */
    public function getDispatcher(): ?EventDispatcherInterface
    {
        return $this->dispatcher;
    }
}