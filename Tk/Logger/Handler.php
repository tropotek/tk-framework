<?php
namespace Tk\Logger;

use Tk\Config;
use Tk\Log;

final class Handler extends LoggerInterface
{

    public    bool  $noLogEnabled = true;
    private   array $handlers     = [];


    public function addHandler(\Psr\Log\LoggerInterface $logger): void
    {
        $this->handlers[] = $logger;
    }

    public function setEnableNoLog(bool $b): void
    {
        $this->noLogEnabled = $b;
    }

    public function log($level, $message, array $context = array()): void
    {
        if ($this->noLogEnabled) {
            // No log when using nolog in query param
            if (($_GET[Log::NO_LOG] ?? '') == Log::NO_LOG) return;

            // No logs for api calls (comment out when testing API`s)
            if (str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/')) return;
        }

        foreach ($this->handlers as $logger) {
            $logger->log($level, $message, $context);
        }
    }

    private function getCallerLine(int $shift = 2): string
    {
        $bt = debug_backtrace();
        for($i = 0; $i < $shift; $i++) array_shift($bt);
        $caller = array_shift($bt);
        $str = '';
        if ($caller) {
            $config = Config::instance();
            $line = $caller['line'];
            $file = str_replace($config->getBasePath(), '', $caller['file']);
            $str = sprintf('[%s:%s] ', $file, $line);
        }
        return $str;
    }

}