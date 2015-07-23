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
//use Symfony\Component\HttpFoundation\Response;
use Tk\Dom\Modifier\Modifier;
use Dom\Template;


class DomModifierHandler implements EventSubscriberInterface
{
    /**
     * @var Modifier
     */
    private $domModifier = null;

    /**
     *
     * @param Modifier $dm
     */
    function __construct(Modifier $dm)
    {
        $this->domModifier = $dm;
    }

    /**
     *
     * @param GetResponseForControllerResultEvent $event
     */
    public function onView(GetResponseForControllerResultEvent $event)
    {
        /* @var $template Template */
        $template = $event->getControllerResult();
        if ($template instanceof Template) {
            $this->domModifier->execute($template->getDocument());
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