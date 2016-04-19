<?php
namespace Tk\Routing;

/**
 * Class Route
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Route
{   
    use \Tk\Traits\Parameter;

    /**
     * @var string
     */
    protected $routeUri = '';

    /**
     * Route constructor.
     *
     * @param $routeUri
     * @param array $params
     */
    public function __construct($routeUri, $params = array())
    {
        $this->routeUri = $routeUri;
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function getRouteUri()
    {
        return $this->routeUri;
    }
    
    

}