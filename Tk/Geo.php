<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk;

/**
 * A geography calss latitude longditude and zoom holder
 *
 *
 * @package Tk
 */
class Geo extends Object
{

    protected $lat = null;
    
    protected $lng = null;

    protected $zoom = 12;
    

    /**
     * 
     *
     * @param float $lat
     * @param float $lng
     * @param int $zoom
     */
    public function __construct($lat = 0, $lng = 0, $zoom = 12)
    {
        $this->setLat($lat);
        $this->setLng($lng);
        $this->setZoom($zoom);
    }

    /**
     * 
     *
     * @param float $lat
     * @param float $lng
     * @param int $zoom
     * @return \Tk\Geo
     */
    static function create($lat = 0, $lng = 0, $zoom = 12)
    {
        return new self($lat, $lng, $zoom);
    }

    /**
     * Create from a json string
     *
     * @param string $json
     * @return \Tk\Geo
     */
    static function jsonDecode($json)
    {
        $obj = json_decode($json);
        if (!$obj) {
            throw new Exception('Invalid JSON string. Cannot Parse Geo Object.');
        }
        $zoom = 16;
        if ($obj->zoom)
            $zoom = $obj->zoom;
        return new self($obj->lat, $obj->lng, $zoom);
    }

    

    /**
     * Returns the latitude in degrees.
     *
     * @return float
     */
    public function getLat()
    {
        return $this->lat;
    }
    /**
     * 
     * @param float $lat
     * @return \Tk\Geo
     */
    public function setLat($lat)
    {
        $this->lat = (float)$lat;
        return $this;
    }

    /**
     * Returns the longitude in degrees.
     *
     * @return float
     */
    public function getLng()
    {
        return $this->lng;
    }
    
    /**
     * 
     * @param float $lng
     * @return \Tk\Geo
     */
    public function setLng($lng)
    {
        $this->lng = (float)$lng;
        return $this;
    }
    
   
    /**
     * Returns the map zoom
     *
     * @return int
     */
    public function getZoom()
    {
        return $this->zoom;
    }
    
    /**
     * 
     * @param int $zoom
     * @return \Tk\Geo
     */
    public function setZoom($zoom)
    {
        $this->zoom = (integer)$zoom;
        return $this;
    }

    /**
     * Converts to string representation.
     *
     * @return string
     */
    public function toString()
    {
        return sprintf("{lat: %f, lng:  %f, zoom: %d}", $this->getLat(), $this->getLng(), $this->getZoom());
    }
    
    
    

}