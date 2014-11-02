<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Dispatcher;

/**
 * Dispatcher
 * This objects task is to execute queued Dispatcher objects until
 * a class object can be returned that can be executed as a module
 *
 * @package Tk\Dispatcher
 */
class Dispatcher extends \Tk\Object
{

    /**
     * Controller Class
     * @var string
     */
    protected $class = '';

    /**
     * Controller Method
     * @var string
     */
    protected $method = 'execute';

    /**
     * Controller Method
     * @var string
     */
    protected $output = 'html';

    /**
     * Controller method parameters
     * @var string
     */
    protected $params = array();

    /**
     * @var \Tk\Url
     */
    protected $requestUrl = null;

    /**
     * @var \Tk\FrontController
     */
    protected $frontController = null;



    /**
     * constructor
     *
     * @param \Tk\Url $requestUrl
     */
    public function __construct($requestUrl = null)
    {
        if (!$requestUrl instanceof \Tk\Url) {
            $requestUrl = $this->getUri();
        }
        $this->requestUrl = $requestUrl;
    }

    /**
     * Execute
     *
     * @return \Tk\Dispatcher\Dispatcher
     */
    public function execute()
    {
        $this->class = '';
        $this->method = 'execute';
        $this->output = 'html';
        $this->params = array();
        $this->notify();
        tklog('Dispatcher Module - ' . $this->class.' - ' . $this->output);
        return $this;
    }


    /**
     * Get the frontController
     * Used for advances setups
     *
     * @return \Tk\FrontController
     */
    public function getFrontController()
    {
        return $this->frontController;
    }

    /**
     * Get the current request url
     *
     * @return \Tk\Url
     */
    public function getRequestUrl()
    {
        return $this->requestUrl;
    }

    /**
     * Add/set a value to the params list
     *
     * @param string $name
     * @param mixed $value
     * @return \Tk\Dispatcher\Dispatcher
     */
    public function set($name, $value)
    {
        $this->params[$name] = $value;
        return $this;
    }

    /**
     * Get a param from the list
     *
     * @param string $name
     * @return mixed
     */
    public function get($name)
    {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        }
    }

    /**
     * Test if a param exists in the list
     *
     * @param string $name
     * @return mixed
     */
    public function exists($name)
    {
        if (isset($this->params[$name])) {
            return true;
        }
        return false;
    }



    /**
     * Overwite and set the params array
     *
     * @param array $array
     * @return \Tk\Dispatcher\Dispatcher
     */
    public function setParams($array)
    {
        $this->params = $array;
        return $this;
    }

    /**
     * Get the method
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set the method
     *
     * @param string $method
     * @return \Tk\Dispatcher\Dispatcher
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Get the method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set the output
     *  preg_match('/^(json|xml|html|.....)$/', trim($output));
     *
     * @param string $output
     * @return \Tk\Dispatcher\Dispatcher
     */
    public function setOutput($output)
    {
        $this->output = trim($output);
        return $this;
    }

    /**
     * Get the output
     *
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Set the Module controller class name
     *
     * @param string $class
     * @param bool $overwrite     (Optional) Default false
     * @return \Tk\Dispatcher\Dispatcher
     */
    public function setClass($class, $overwrite = false)
    {
        if (!$this->class || $overwrite) {
            $this->class = $class;
        }
        return $this;
    }

    /**
     * Get the module class name
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

}