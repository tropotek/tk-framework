<?php
namespace Tk\Logger;

use Tk\Log;

final class Handler extends LoggerInterface
{

    public  bool  $noLogEnabled = true;
    private array $loggers     = [];


    public function addLogger(LoggerInterface $logger): void
    {
        $this->loggers[] = $logger;
    }

    public function setEnableNoLog(bool $b): void
    {
        $this->noLogEnabled = $b;
    }

    public function log($level, $message, array $context = array()): void   /** @phpstan-ignore-line */
    {
        if ($this->noLogEnabled) {
            // No log when using 'nolog' in query param
            if (($_GET[Log::NO_LOG] ?? '') == Log::NO_LOG) return;

            // No logs for api calls (comment out when testing API`s)
            if (str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/')) return;
        }

        foreach ($this->loggers as $logger) {
            $logger->log($level, $message, $context);
        }
    }

}