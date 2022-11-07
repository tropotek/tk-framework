<?php

namespace Tk\Traits;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

trait EventDispatcherTrait
{

    protected null|EventDispatcherInterface $_dispatcher = null;


    public function setDispatcher(?EventDispatcherInterface $dispatcher): static
    {
        $this->_dispatcher = $dispatcher;
        return $this;
    }

    public function getDispatcher(): ?EventDispatcherInterface
    {
        return $this->_dispatcher;
    }

}