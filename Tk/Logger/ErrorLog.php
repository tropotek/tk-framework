<?php

namespace Tk\Logger;


class ErrorLog extends LoggerInterface
{

    public function log(mixed $level, mixed $message, array $context = []): void
    {
        if (self::RFC_5424_LEVELS[$level] > $this->level) return;
        error_log($message);
    }
}