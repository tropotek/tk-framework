<?php
/**
 * Created by PhpStorm.
 * User: mifsudm
 * Date: 5/12/15
 * Time: 11:20 AM
 */

namespace Tk\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpFoundation\Response;

class StringResponseHandler implements EventSubscriberInterface
{

    public function onView(GetResponseForControllerResultEvent $event)
    {
        $result = $event->getControllerResult();
        if (is_string($result)) {
            $event->setResponse(new Response($result));
        }
    }

    public static function getSubscribedEvents()
    {
        return array('kernel.view' => 'onView');
    }

}