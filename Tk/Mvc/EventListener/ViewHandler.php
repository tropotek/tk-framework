<?php
namespace Tk\Mvc\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ViewHandler implements EventSubscriberInterface
{

    public function onView(ViewEvent $event): void
    {
        $response = $event->getControllerResult();

        if (is_string($response)) {
            $event->setResponse(new Response($response));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['onView', -100]
        ];
    }
}