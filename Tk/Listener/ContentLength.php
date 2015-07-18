<?php
/**
 * Created by PhpStorm.
 * User: mifsudm
 * Date: 5/12/15
 * Time: 10:16 AM
 */

namespace Tk\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tk\Event\ResponseEvent;

class ContentLength implements EventSubscriberInterface
{

    /**
     * @param ResponseEvent $event
     */
    public function onResponse(ResponseEvent $event)
    {
        $response = $event->getResponse();
        $headers = $response->headers;

        if (!$headers->has('Content-Length') && !$headers->has('Transfer-Encoding')) {
            $headers->set('Content-Length', strlen($response->getContent()));
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array('response' => array('onResponse', -255));
    }

}