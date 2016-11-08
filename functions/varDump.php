<?php

/**
 * logger Helper function
 * Replacement for var_dump();
 *
 * @return string
 */
function vd() {
    $config = \Tk\Config::getInstance();
    $line = current(debug_backtrace());
    /** @var \Psr\Log\LoggerInterface $log */
    $log =  $config->getLog();
    if (!$log) $log = new \Psr\Log\NullLogger();

    $path = str_replace($config->getSitePath(), '', $line['file']);
    $str = sprintf('vd(%s [%s])', $path, $line['line']) . "\n";
    $str .= \Tk\Debug\VarDump::getInstance($config->getSitePath())->makeDump(func_get_args());

    if (!$config->isCli()) {
        $log->info($str);
    } else {
        error_log($str);
    }
    return $str;
}

/**
 * logger Helper function with stack trace.
 * Replacement for var_dump();
 *
 * @return string
 */
function vdd() {
    $config = \Tk\Config::getInstance();
    $line = current(debug_backtrace());
    /** @var \Psr\Log\LoggerInterface $log */
    $log =  $config->getLog();
    if (!$log) $log = new \Psr\Log\NullLogger();

    $path = str_replace($config->getSitePath(), '', $line['file']);
    $str = sprintf('vdd(%s [%s])', $path, $line['line']) . "\n";
    $str .= \Tk\Debug\VarDump::getInstance($config->getSitePath())->makeDump(func_get_args(), true);

    if (!$config->isCli()) {
        $log->info($str);
    } else {
        error_log($str);
    }
    return $str;
}