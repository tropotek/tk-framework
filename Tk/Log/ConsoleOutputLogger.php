<?php

namespace Tk\Log;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleOutputLogger implements LoggerInterface
{
    private OutputInterface $output;


    public function __construct(OutputInterface $output)
    {
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

    public function emergency($message, array $context = array())
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert($message, array $context = array())
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical($message, array $context = array())
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error($message, array $context = array())
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning($message, array $context = array())
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice($message, array $context = array())
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info($message, array $context = array())
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug($message, array $context = array())
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }
}