<?php

namespace Tk\Logger;


class ErrorLog extends LoggerInterface
{

    public function log(mixed $level, mixed $message, array $context = []): void
    {
        if (!$this->canLog($level)) return;
        error_log($message);
    }
}