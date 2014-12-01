<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk;

use \Tk\Log\Log;

/**
 * A controller to manage the processing of system events.
 * System events are fired in secuene and if an event object is registered
 * with the event name it will be executed.
 *
 * @see http://r.je/mvc-php-front-controller.html
 * @package Tk
 */
class FrontController extends Object implements Command\Iface
{
    /**
     * @var int
     */
    static $scriptTime = null;



    /**
     * Get the current script running time in seconds
     *
     * @return string
     */
    static function scriptDuration()
    {
    	return (string)(microtime(true)-self::$scriptTime);
    }

    /**
     * Process the request and response of page requested
     *
     */
    public function execute()
    {
        switch (strtoupper($this->getRequest()->getRequestMethod())) {
            case 'GET' :
            case 'POST' :
                $this->notify('preExecute');
                $this->notify();
                $this->notify('postExecute');
                break;
            case 'HEAD':
            case 'OPTIONS':
            case 'PROPFIND':
                // Do nothing
                break;
            default :
                throw new Exception("Unknown request method `" . $this->getRequest()->getRequestMethod() . "`.");
        }
        tklog("End Script, Shutting down...");
    }


    /**
     * Display a fatal error.
     *
     * @param Exception $e
     */
    public function showServerError(\Exception $e)
    {
        $this->getConfig()->getResponse()->reset();
        $msg = '';
        $dump = '';
        if ($this->getConfig()->isDebug()) {
            $msg = '<p>'.$e->getMessage().'</p>';
            $dump = htmlentities($e->__toString());
        } else {
            $msg = "<h2>Sorry Page Down.</h2> \n<p>The site administrator has been notified of the problem. \n\nPlease check back soon.</p>";
        }

        // Write error to screen and log...
        tklog('UNCAUGHT EXCEPTION: ' . $e->__toString(), \Tk\Log\Log::ERROR);
        $this->getConfig()->getResponse()->sendError($msg, Response::SC_INTERNAL_SERVER_ERROR, $dump);

    }

    /**
     * Send a 404 page not found error page.
     */
    public function showNotFoundError()
    {
        $this->getConfig()->getResponse()->sendError("Sorry, an error has occurred. Requested page not found!.", Response::SC_NOT_FOUND);
    }

}

if (!FrontController::$scriptTime)
    FrontController::$scriptTime = microtime(true);
