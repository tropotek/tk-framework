<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Controller;

/**
 *
 * @package Tk\Controller
 */
class StartLog extends \Tk\Object implements Iface
{

    /**
     *
     * @param \Tk\FrontController $obj
     */
    public function update($obj)
    {
        $log = $this->getConfig()->getLog();

        // TODO: Not sure if I want to permanently include this
//        if (preg_match('/\/(ajax|widget)\/.*/i', $this->getUri()->getPath(true), $regs) && $this->getConfig()->isDebug() ) {
//            $log->setEnabled(false);
//            return;
//        }

        $request = $this->getRequest();

        $log->writeLine('===============================================================================');
        $log->writeLine('Date:       ' . \Tk\Date::create()->toString('Y-m-d'));

        $qs = '';
        if ($request->getUri()->getQueryString()) $qs = '?' . $request->getUri()->getQueryString();
        $log->writeLine('Request:    ' . $request->getUri()->getPath(true) . $qs);

        if (strlen($this->getConfig()->getSiteUrl()) > 1) {
            $log->writeLine('Site URL:   ' . $this->getConfig()->getSiteUrl());
        }
        $log->writeLine('Domain:     ' . $request->getServer('HTTP_HOST'));
        if ($request->getRemoteAddr()) {
            $log->writeLine('Client:     ' . $request->getRemoteAddr());
        }
        if ($request->getUserAgent()) {
            $log->writeLine('Agent:      ' . $request->getUserAgent());
        }
        $log->writeLine('Method:     ' . $request->getRequestMethod());
        $log->writeLine('PHP Ver:     ' . PHP_VERSION);
        //$log->writeLine('Include:  ' . ini_get('include_path'));
        $log->writeLine('Ses Name:   ' . $this->getConfig()->get('session.name'));
        $log->writeLine('-------------------------------------------------------------------------------');
        $log->writeLine('System Initialised');
        tklog($this->getClassName() . '::update()');


        register_shutdown_function(array($this, 'shutdown'));
    }

    /**
     * System Cleanup
     * This method will be called after the session cleanup command
     *
     */
    public function shutdown()
    {
        $log = $this->getConfig()->getLog();
        static $done = false;
        if (!$done) {
            $done = true;
            // Get Script run time and display.
            $log->writeLine('System Shutting Down.');
            $log->writeLine('-------------------------------------------------------------------------------');
            $log->writeLine('Peek Mem: ' . \Tk\Path::bytes2String(memory_get_peak_usage(), 4));
            // TODO: Build autolodar stub...

            //$log->writeLine('Class Loads: ' . \Tk\Autoloader::getLookupCount());
            $log->writeLine('Script Time: ' . round(\Tk\FrontController::scriptDuration(), 4) . ' sec');
            $log->writeLine('==============================================================================='."\n\n");
            exit();
        }
    }

}