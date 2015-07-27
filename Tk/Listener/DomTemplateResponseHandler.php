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

/**
 * If a Dom/Template is returned from a controller action.
 * Then this Event processes the template ready for output.
 *
 * Class DomTemplateResponseHandler
 * @package Tk\Listener
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
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array('kernel.view' => 'onView');
    }

}