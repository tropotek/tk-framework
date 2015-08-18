<?php
namespace Tk\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpFoundation\Response;
use Tk\Dom\Modifier\Modifier;
use Dom\Template;

/**
 * Class DomModifierHandler
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
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
        $result = $event->getControllerResult();
        if ($result instanceof Template) {
            $this->domModifier->execute($result->getDocument());
        }
        if ($result instanceof \Dom\Renderer\Iface) {
            $this->domModifier->execute($result->getTemplate()->getDocument());
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