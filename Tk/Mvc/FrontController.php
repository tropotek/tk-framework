<?php
namespace Tk\Mvc;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\TerminableInterface;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\Loader\PhpFileLoader;
use Tk\Log;
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

    public function handle(Request $request, int $type = HttpKernelInterface::MAIN_REQUEST, bool $catch = true): Response
    {
        $response = parent::handle($request, $type, $catch);

        return $response;
    }

}