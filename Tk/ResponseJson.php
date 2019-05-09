<?php
namespace Tk;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class ResponseJson extends Response
{

    public static function createJson($json = null, $status = self::HTTP_OK, $headers = array()) {
        
        if (!self::isJson($json)) {
            $json = json_encode($json);
            if ($json === false) {
                $json = json_encode(array('error' => 'Cannot convert response value to JSON string.'));
                $status = self::HTTP_INTERNAL_SERVER_ERROR;
            }
        }
        $obj = new static($json, $status, $headers);
        $obj->addHeader('Cache-Control', 'no-cache, must-revalidate');
        $obj->addHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
        $obj->addHeader('Content-type', 'application/json');
        return $obj;
    }


    public static function isJson($string) 
    {
        if (!is_string($string)) return false;
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

}