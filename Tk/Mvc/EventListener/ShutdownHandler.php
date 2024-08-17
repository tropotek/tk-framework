<?php
namespace Tk\Mvc\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Tk\Log;

class ShutdownHandler implements EventSubscriberInterface
{
    protected float $scriptStartTime = 0;


    function __construct(float $scriptStartTime = 0)
    {
        $this->scriptStartTime = $scriptStartTime;
        register_shutdown_function(array($this, 'onShutdown'));
    }

    public function onShutdown()
    {
        // Echo the final line
        if (!StartupHandler::$SCRIPT_CALLED) return;
        $this->info(StartupHandler::$SCRIPT_END . \PHP_EOL);
    }

    public function onTerminate(TerminateEvent $event)
    {
        if (!StartupHandler::$SCRIPT_CALLED) return;
        $this->info(StartupHandler::$SCRIPT_LINE);
        $this->info(sprintf('Time: %s sec    Peek Mem: %s',
            round($this->scriptDuration(), 4),
            \Tk\FileUtil::bytes2String(memory_get_peak_usage(), 4)
        ));
    }

    private function info(string $str)
    {
        Log::info($str);
    }

    private function debug(string $str)
    {
        Log::debug($str);
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