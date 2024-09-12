<?php
namespace Tk\Mvc\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionListener implements EventSubscriberInterface
{

    protected mixed $controller;
    protected bool $debug;
    /**
     * @var array<class-string, array{log_level: string|null, status_code: int<100,599>|null}>
     */
    protected array $exceptionsMapping;

    /**
     * @param array<class-string, array{log_level: string|null, status_code: int<100,599>|null}> $exceptionsMapping
     */
    public function __construct(mixed $controller, bool $debug = false, array $exceptionsMapping = [])
    {
        $this->controller = $controller;
        $this->debug = $debug;
        $this->exceptionsMapping = $exceptionsMapping;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        if (null === $this->controller) return;

        $throwable = $event->getThrowable();
        $request = $this->duplicateRequest($throwable, $event->getRequest());

        try {
            $response = $event->getKernel()->handle($request, HttpKernelInterface::SUB_REQUEST, false);
        } catch (\Exception $e) {
            $prev = $e;
            do {
                if ($throwable === $wrapper = $prev) {
                    throw $e;
                }
            } while ($prev = $wrapper->getPrevious());

            $prev = new \ReflectionProperty($wrapper instanceof \Exception ? \Exception::class : \Error::class, 'previous');
            $prev->setValue($wrapper, $throwable);

            throw $e;
        }

        $event->setResponse($response);

        if ($this->debug) {
            $event->getRequest()->attributes->set('_remove_csp_headers', true);
        }
    }

    public function removeCspHeader(ResponseEvent $event): void
    {
        if ($this->debug && $event->getRequest()->attributes->get('_remove_csp_headers', false)) {
            $event->getResponse()->headers->remove('Content-Security-Policy');
        }
    }

    public function onControllerArguments(ControllerArgumentsEvent $event)
    {
        $e = $event->getRequest()->attributes->get('exception');
        if (!$e instanceof \Throwable || false === $k = array_search($e, $event->getArguments(), true)) {
            return;
        }

        //$r = new \ReflectionFunction(\Closure::fromCallable($event->getController()));
        $r = new \ReflectionFunction($event->getController()(...));
        $r = $r->getParameters()[$k] ?? null;

        if ($r && (!($r = $r->getType()) instanceof \ReflectionNamedType)) {
            $arguments = $event->getArguments();
            $arguments[$k] = $e;
            $event->setArguments($arguments);
        }
    }

    /**
     * Clones the request for the exception.
     */
    protected function duplicateRequest(\Throwable $exception, Request $request): Request
    {
        $attributes = [
            '_controller' => $this->controller,
            'e' => $exception,
            'logger' =>  null,
        ];
        $request = $request->duplicate(null, null, $attributes);
        $request->setMethod('GET');
        return $request;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => 'onControllerArguments',
            KernelEvents::EXCEPTION => ['onKernelException', -128],
            KernelEvents::RESPONSE => ['removeCspHeader', -128],
        ];
    }

}