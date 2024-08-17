<?php

namespace Tk\Logger;

use Psr\Log\LogLevel;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleLog extends LoggerInterface
{
    private OutputInterface $output;


    public function __construct(OutputInterface $output, string $level = self::DEBUG)
    {
        parent::__construct($level);
        $this->output = $output;
    }

    public function log($level, $message, array $context = array())
    {
        $levelMap = [
            LogLevel::EMERGENCY => OutputInterface::OUTPUT_NORMAL,
            LogLevel::ALERT     => OutputInterface::OUTPUT_NORMAL,
            LogLevel::CRITICAL  => OutputInterface::OUTPUT_NORMAL,
            LogLevel::ERROR     => OutputInterface::OUTPUT_NORMAL,
            LogLevel::WARNING   => OutputInterface::VERBOSITY_VERBOSE,
            LogLevel::NOTICE    => OutputInterface::VERBOSITY_VERY_VERBOSE,
            LogLevel::INFO      => OutputInterface::VERBOSITY_VERY_VERBOSE,
            LogLevel::DEBUG     => OutputInterface::VERBOSITY_DEBUG
        ];
        $this->output->writeln($message, $levelMap[$level]);
    }
}