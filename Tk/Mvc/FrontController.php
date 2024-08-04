<?php
namespace Tk\Mvc;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\HttpKernel;
use Tk\Traits\SystemTrait;

class FrontController extends HttpKernel
{
    use SystemTrait;

    public function __construct()
    {
        $factory = $this->getSystem()->getFactory();
        parent::__construct(
            $factory->getEventDispatcher(),
            $factory->getControllerResolver(),
            $factory->getRequestStack(),
            $factory->getArgumentResolver()
        );
    }

}