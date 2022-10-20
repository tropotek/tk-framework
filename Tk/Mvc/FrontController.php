<?php
namespace Tk\Mvc;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\TerminableInterface;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
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

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        vd();
//        $routes->import('../config/{routes}/'.$this->environment.'/*.yaml');
//        $routes->import('../config/{routes}/*.yaml');
//
//        if (is_file(\dirname(__DIR__).'/config/routes.yaml')) {
//            $routes->import('../config/routes.yaml');
//        } elseif (is_file($path = \dirname(__DIR__).'/config/routes.php')) {
//            (require $path)($routes->withPath($path), $this);
//        }
    }

    public function handle(Request $request, int $type = HttpKernelInterface::MAIN_REQUEST, bool $catch = true): Response
    {
        $response = parent::handle($request, $type, $catch);

        return $response;
    }

}