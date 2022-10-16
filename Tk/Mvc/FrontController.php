<?php
namespace Tk\Mvc;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\TerminableInterface;
use Tk\Log;
use Tk\Traits\SystemTrait;

/**
 *
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
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

    public function handle(Request $request, $type = HttpKernelInterface::MAIN_REQUEST, $catch = true) {
        $response = parent::handle($request, $type, $catch);

        return $response;
    }

}