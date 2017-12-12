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
    /* @var \Psr\Log\LoggerInterface $log */
    $log =  $config->getLog();
    if (!$log) $log = new \Psr\Log\NullLogger();

    $vd = \Tk\Debug\VarDump::getInstance($config->getSitePath());
    $path = str_replace($config->getSitePath(), '', $line['file']);
    $str = '';
    $str .= "\n";
    //$str = sprintf('vd(%s [%s])', $path, $line['line']) . "\n";
    $str .= $vd->makeDump(func_get_args());
    $str .= sprintf('vd(%s) %s [%s];', implode(', ', $vd->getTypeArray(func_get_args())), $path, $line['line']) . "\n";

    //if (!$config->isCli()) {
    if (!$log instanceof \Psr\Log\NullLogger) {
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
    /* @var \Psr\Log\LoggerInterface $log */
    $log = $config->getLog();
    if (!$log) $log = new \Psr\Log\NullLogger();

    $vd = \Tk\Debug\VarDump::getInstance($config->getSitePath());
    $path = str_replace($config->getSitePath(), '', $line['file']);
    $str = '';
    $str .= "\n";
    $str .= $vd->makeDump(func_get_args(), true);
    $str .= sprintf('vdd(%s) %s [%s]', implode(', ', $vd->getTypeArray(func_get_args())), $path, $line['line']) . "\n";

    //if (!$config->isCli()) {
    if (!$log instanceof \Psr\Log\NullLogger) {
        $log->info($str);
    } else {
        error_log($str);
    }
    return $str;
}