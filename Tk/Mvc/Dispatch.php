<?php
namespace Tk\Mvc;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tk\Mvc\EventListener\ShutdownHandler;
use Tk\Mvc\EventListener\StartupHandler;
use Tk\Traits\ConfigTrait;
use Tk\Traits\FactoryTrait;
use Tk\Traits\SystemTrait;

/**
 * This object sets up the EventDispatcher and
 * attaches all the listeners required for your application.
 *
 * Subclass this object in your App (to setup a Tk framework) and then override the Factory method
 * Factory::initDispatcher()
 */
class Dispatch
{
    use SystemTrait;

    protected ?EventDispatcherInterface $dispatcher = null;


    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        $this->init();
    }

    private function init()
    {
        $this->commonInit();
        if ($this->getSystem()->isCli()) {
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
        if ($this->getConfig()->isDev()) {
            $this->getDispatcher()->addSubscriber(new StartupHandler());
            $this->getDispatcher()->addSubscriber(new ShutdownHandler($this->getConfig()->get('script.start.time')));
        }
    }

    /**
     * Called this when executing http requests
     */
    protected function httpInit()
    {
        $this->getDispatcher()->addSubscriber(new \Symfony\Component\HttpKernel\EventListener\RouterListener(
            $this->getFactory()->getRouteMatcher(),
            $this->getFactory()->getRequestStack()
        ));

        $this->getDispatcher()->addSubscriber(new \Tk\Mvc\EventListener\LogExceptionListener(
            $this->getConfig()->isDebug()
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