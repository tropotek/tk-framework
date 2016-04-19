<?php
namespace Tk\Routing;

/**
 * Class RouteCollection
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class RouteCollection
{
    /**
     * @var RouteCollection
     */
    static $instance = null;

    /**
     * @var array
     */
    public $routeList = array();

    
    
    /**
     * Get an instance of this object
     *
     * @return RouteCollection
     */
    static function getInstance()
    {
        if (static::$instance == null) {
            static::$instance = new static();
        }
        return static::$instance;
    }






    /**
     * @param string $routeUri
     * @return Route
     */
    public function getRoute($routeUri)
    {
        return $this->routeList[$routeUri];
        /** @var Route $route */
//        foreach($this->routeList as $route) {
//            if ($route->getRouteUri() == $routeUri) {
//                return $route;
//            }
//        }
    }
    
    /**
     * @param Route $route
     * @return $this
     */
    public function addRoute(Route $route)
    {
        $this->routeList[$route->getRouteUri()] = $route;
        return $this;
    }

    /**
     * @param $routeUri
     * @return $this
     */
    public function removeRoute($routeUri)
    {
        unset($this->routeList[$routeUri]);
        return $this;
    }
    
    
    
    
    
    
}