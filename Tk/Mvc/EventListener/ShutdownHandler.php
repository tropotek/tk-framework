<?php
namespace Tk\Mvc\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ShutdownHandler implements EventSubscriberInterface
{
    private LoggerInterface $logger;

    protected float $scriptStartTime = 0;


    function __construct(LoggerInterface $logger, float $scriptStartTime = 0)
    {
        $this->logger = $logger;
        $this->scriptStartTime = $scriptStartTime;
        register_shutdown_function(array($this, 'onShutdown'));
    }

    public function onShutdown()
    {
        // Echo the final line
        if (!StartupHandler::$SCRIPT_CALLED) return;
        $this->out(StartupHandler::$SCRIPT_END . \PHP_EOL);
    }

    public function onTerminate(TerminateEvent $event)
    {
        if (!StartupHandler::$SCRIPT_CALLED) return;
        $this->out(StartupHandler::$SCRIPT_LINE);
        $this->out('Load Time: ' . round($this->scriptDuration(), 4) . ' sec');
        $this->out('Peek Mem:  ' . \Tk\FileUtil::bytes2String(memory_get_peak_usage(), 4));

        $this->out('Response Headers:');
        $this->out('  HTTP Code: ' . http_response_code() . ' ');

    }

    private function out($str)
    {
        $this->logger->info($str);
    }

    /**
     * Get the current script running time in seconds
     */
    protected function scriptDuration(): string
    {
        return (string)(microtime(true) - $this->scriptStartTime);
    }

    public static function getSubscribedEvents()
    {
        return array(KernelEvents::TERMINATE => 'onTerminate');
    }

}