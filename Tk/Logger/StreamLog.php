<?php

namespace Tk\Logger;

use Tk\Exception;

class StreamLog extends LoggerInterface
{
    protected string $filepath = '';

    public function __construct(string $filepath, string $level = self::DEBUG)
    {
        parent::__construct($level);

        if (!is_file($filepath) && is_dir(dirname($filepath))) {
            file_put_contents($filepath, ''); // create new log
        }
        if (!is_writable($filepath)) {
            throw new Exception("cannot write to file: {$filepath}");
        }

        $this->filepath = $filepath;
    }

    public function log(mixed $level, mixed $message, array $context = []): void
    {
        if (self::RFC_5424_LEVELS[$level] > $this->level) return;

        $line = $this->format($level, $message, $context) . PHP_EOL;
        if (is_writable($this->filepath)) {
            file_put_contents($this->filepath, $line, FILE_APPEND | LOCK_EX);
        }
    }

}