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

    $vd = \Tk\Debug\VarDump::getInstance($config->getSitePath());
    $path = str_replace($config->getSitePath(), '', $line['file']);
    $str = '';
    $str .= "\n";
    //$str = sprintf('vd(%s [%s])', $path, $line['line']) . "\n";
    $str .= $vd->makeDump(func_get_args());
    $str .= sprintf('vd(%s) %s [%s];', implode(', ', $vd->getTypeArray(func_get_args())), $path, $line['line']) . "\n";

    /* @var \Psr\Log\LoggerInterface $log */
    $log =  $config->getLog();
    if ($log) {
        $log->info($str);
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

    $vd = \Tk\Debug\VarDump::getInstance($config->getSitePath());
    $path = str_replace($config->getSitePath(), '', $line['file']);
    $str = '';
    $str .= "\n";
    $str .= $vd->makeDump(func_get_args(), true);
    $str .= sprintf('vdd(%s) %s [%s]', implode(', ', $vd->getTypeArray(func_get_args())), $path, $line['line']) . "\n";

    /* @var \Psr\Log\LoggerInterface $log */
    $log =  $config->getLog();
    if ($log) {
        $log->info($str);
    }
    return $str;
}