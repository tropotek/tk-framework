<?php
namespace Tk\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpFoundation\Response;

/**
 * If a \Dom\Template object is returned from a controller
 *
 * This event creates a valid response from the \Dom\Template object
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
class DomTemplateResponseHandler implements EventSubscriberInterface
{
    /**
     *
     * @param GetResponseForControllerResultEvent $event
     */
    public function onView(GetResponseForControllerResultEvent $event)
    {
        $result = $event->getControllerResult();
        if ($result instanceof \Dom\Template) {
            $event->setResponse(new Response($result->toString()));
        }
        if ($result instanceof \Dom\Renderer\Iface) {
            $event->setResponse(new Response($result->getTemplate()->toString()));
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array('kernel.view' => 'onView');
    }

}