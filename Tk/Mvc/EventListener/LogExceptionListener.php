<?php
namespace Tk\Mvc\EventListener;

use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class LogExceptionListener implements EventSubscriberInterface
{

    protected LoggerInterface $logger;

    protected bool $debug = false;


    public function __construct(LoggerInterface $logger, bool $debug = false)
    {
        $this->logger = $logger;
        $this->debug = $debug;
    }

    public function onException(ExceptionEvent $event)
    {
        $this->logException($event->getThrowable());
    }

    public function onConsoleError(ConsoleErrorEvent $event)
    {
        $this->logException($event->getError());
    }

    protected function logException(\Throwable $e)
    {
        if ($e instanceof ResourceNotFoundException || $e instanceof NotFoundHttpException) {
            $this->logger->error(self::getCallerLine($e) . $e->getMessage());
        } else {
            if ($this->debug) {
                if ($e instanceof \Tk\WarningException) {
                    $this->logger->warning(self::getCallerLine($e) . $e->__toString());
                } else {
                    $this->logger->error(self::getCallerLine($e) . $e->__toString());
                }
            } else {
                if ($e instanceof \Tk\WarningException) {
                    $this->logger->warning(self::getCallerLine($e) . $e->getMessage());
                } else {
                    $this->logger->error(self::getCallerLine($e) . $e->getMessage());
                }
            }
        }
    }

    private static function getCallerLine(\Throwable $e): string
    {
        $config = \Tk\Config::instance();
        $line = $e->getLine();
        $file = str_replace($config->getBasePath(), '', $e->getFile());
        return sprintf('[%s:%s] ', $file, $line);
    }

    public static function getSubscribedEvents()
    {
        return array(
            'console.error' => 'onConsoleError',
            KernelEvents::EXCEPTION => 'onException'
        );
    }

}