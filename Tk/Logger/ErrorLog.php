<?php

namespace Tk\Logger;


class ErrorLog extends LoggerInterface
{

    public function log($level, $message, array $context = []): void    /** @phpstan-ignore-line */
    {
        if (!$this->canLog($level)) return;
        error_log($message);
    }
}