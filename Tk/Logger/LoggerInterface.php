<?php
namespace Tk\Logger;

use Psr\Log\LogLevel;

abstract class LoggerInterface extends LogLevel implements \Psr\Log\LoggerInterface
{
    /**
     * error level abbreviation list
     */
    protected const ABR = [
        self::EMERGENCY => 'EMR',
        self::ALERT     => 'ALT',
        self::CRITICAL  => 'CRT',
        self::ERROR     => 'ERR',
        self::WARNING   => 'WRN',
        self::NOTICE    => 'NTC',
        self::INFO      => 'INF',
        self::DEBUG     => 'DBG'
    ];

    /**
     * Mapping between levels numbers defined in RFC 5424
     */
    protected const RFC_5424_LEVELS = [
        self::DEBUG     => 7,
        self::INFO      => 6,
        self::NOTICE    => 5,
        self::WARNING   => 4,
        self::ERROR     => 3,
        self::CRITICAL  => 2,
        self::ALERT     => 1,
        self::EMERGENCY => 0,
    ];

    protected string $level = self::DEBUG;


    public function __construct(string $level = self::DEBUG)
    {
        $this->level = $level;
    }

    protected function format(string $level, string $message, array $context = []): ?string
    {

        // FORMAT = "[%datetime%]%post% %level_name%: %message% %context% %extra%\n";
        $mem = sprintf('[%9s]', \Tk\FileUtil::bytes2String(memory_get_usage(false)));
        $now = \DateTime::createFromFormat('U.u', microtime(true));

        return sprintf('[%s]%s %s %s',
            $now->format("H:i:s.u"),
            $mem,
            self::ABR[$level] ?? 'N/A',
            $message
        );
    }

    public function emergency($message, array $context = array()): void
    {
        $this->log(self::EMERGENCY, $message, $context);
    }

    public function alert($message, array $context = array()): void
    {
        $this->log(self::ALERT, $message, $context);
    }

    public function critical($message, array $context = array()): void
    {
        $this->log(self::CRITICAL, $message, $context);
    }

    public function error($message, array $context = array()): void
    {
        $this->log(self::ERROR, $message, $context);
    }

    public function warning($message, array $context = array()): void
    {
        $this->log(self::WARNING, $message, $context);
    }

    public function notice($message, array $context = array()): void
    {
        $this->log(self::NOTICE, $message, $context);
    }

    public function info($message, array $context = array()): void
    {
        $this->log(self::INFO, $message, $context);
    }

    public function debug($message, array $context = array()): void
    {
        $this->log(self::DEBUG, $message, $context);
    }
}