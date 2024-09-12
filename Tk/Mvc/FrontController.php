<?php
namespace Tk\Mvc;

use Bs\Factory;
use Symfony\Component\HttpKernel\HttpKernel;

class FrontController extends HttpKernel
{

    public function __construct()
    {
        $factory = Factory::instance();
        parent::__construct(
            $factory->getEventDispatcher(),
            $factory->getControllerResolver(),
            $factory->getRequestStack(),
            $factory->getArgumentResolver()
        );
    }

}