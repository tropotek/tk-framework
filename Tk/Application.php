<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk;

/**
 * A mail factory object, just a place to compile/execute all commands for the system....
 *
 * @package Tk
 */
abstract class Application extends \Tk\Object implements \Tk\Command\Iface
{

    /**
     * @var \Tk\FrontController
     */
    protected $frontController = null;



    /**
     *
     * @param \Tk\FrontController $frontController
     */
    public function __construct($frontController)
    {
        $this->frontController = $frontController;
    }

    /**
     *  startup
     *
     */
    abstract protected function startup();


    /**
     * Shutdown the application
     *
     */
    abstract protected function run();


    /**
     * Shutdown the application
     *
     */
    abstract protected function shutdown();



    /**
     * execute
     *
     */
    public function execute()
    {
        try {

            $this->startup();
            $this->run();
            $this->shutdown();
            return $this;

        } catch (\Exception $e) {
            $this->showServerError($e);
        }
    }

    /**
     * Get the applications front controller
     *
     * @return \Tk\FrontController
     */
    public function getFrontController()
    {
        return $this->frontController;
    }

    /**
     * Display a fatal error.
     *
     * @param Exception $e
     */
    public function showServerError(\Exception $e)
    {
        $this->frontController->showServerError($e);
    }

    /**
     * Send a 404 page not found error page.
     *
     * @param \Tk\Url $uri
     */
    public function showNotFoundError($uri)
    {
        $this->frontController->showNotFoundError($uri);
    }

}


