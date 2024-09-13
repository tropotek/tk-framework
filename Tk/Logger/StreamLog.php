<?php
namespace Tk\Logger;

use Tk\FileUtil;

class StreamLog extends LoggerInterface
{
    protected string $filepath = '';

    public function __construct(string $filepath, string $level = self::DEBUG)
    {
        parent::__construct($level);
        $this->filepath = $filepath;
    }

    public function log(mixed $level, mixed $message, array $context = []): void
    {
        if (self::RFC_5424_LEVELS[$level] > $this->level) return;
        if (!is_file($this->filepath)) {
            FileUtil::mkdir(dirname($this->filepath));
            file_put_contents($this->filepath, ''); // create new log
        }

        $line = $this->format($level, $message, $context) . PHP_EOL;
        if (is_writable($this->filepath)) {
            file_put_contents($this->filepath, $line, FILE_APPEND | LOCK_EX);
        }
    }

}