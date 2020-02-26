<?php
namespace Tk;

use Symfony\Component\HttpFoundation\File\UploadedFile;



/**
 * This is a rewritten Request object with the PSR7 interfaces taken into consideration,
 * However you will need to extend these objects to make them completely PSR7 compatible.
 * 
 * The object uses the ArrayAccess interface so that the request object can be used like the $_REQUEST array
 * in situations that do not have the Request object.
 * 
 * 
 * @thought: I am not 100% sure that our libs currently needs to support PSR7 is its entirety.
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Request extends \Symfony\Component\HttpFoundation\Request
{


//    public static function create($params = null, $serverParams = null, $uploadedFiles = null)
//    {
//        $uri = Uri::create();
//        $method = 'GET';
//        if (isset($_SERVER['REQUEST_METHOD'])) {
//            $method = $_SERVER['REQUEST_METHOD'];
//        }
//
//        $headers = Headers::create();
//
//        if ($params === null) {
//            $params = $_REQUEST;
//        }
//
//        if ($serverParams === null) {
//            $serverParams = $_SERVER;
//        }
//
//        $cookies = $_COOKIE;
//
//        if ($uploadedFiles === null) {
//            $uploadedFiles = array();
//            if (!empty($_FILES)) {
//                $uploadedFiles = UploadedFile::parseUploadedFiles($_FILES);
//            }
//        }
//
//        $request = new static($uri, $method, $headers, $params, $serverParams, $cookies, $uploadedFiles);
//        return $request;
//    }


//    public function get($key, $default = null)
//    {
//        return parent::get($key, $default);
//    }

    /**
     * @return \Tk\Uri
     */
    public function getTkUri()
    {
        return Uri::create($this->getUri());
    }

    /**
     * Return all the request params 
     * 
     * @return array
     */
    public function all()
    {
        return $this->request->all();
    }

    /**
     * Check if a request param exists
     * 
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return $this->request->has($key);
    }
    

    /**
     * Retrieve cookies.
     *
     * Retrieves cookies sent by the client to the server.
     *
     * The data MUST be compatible with the structure of the $_COOKIE
     * superglobal.
     *
     * @return array
     */
    public function getCookieParams()
    {
        return $this->cookies->all();
    }


    /**
     * Retrieve query string arguments.
     *
     * Retrieves the deserialized query string arguments, if any.
     *
     * Note: the query params might not be in sync with the URI or server
     * params. If you need to ensure you are only getting the original
     * values, you may need to parse the query string from `getUri()->getQuery()`
     * or from the `QUERY_STRING` server param.
     *
     * @return array
     */
    public function getQueryParams()
    {
        return $this->query->all();
    }
    
    /**
     * Retrieve normalized file upload data.
     *
     * This method returns upload metadata in a normalized tree, with each leaf
     * an instance of Psr\Http\Message\UploadedFileInterface.
     *
     * These values MAY be prepared from $_FILES or the message body during
     * instantiation, or MAY be injected via withUploadedFiles().
     *
     * @return array|UploadedFile[] An array tree of UploadedFileInterface instances; an empty
     *     array MUST be returned if no data is present.
     */
    public function getUploadedFiles()
    {
        return $this->files->all();
    }

    /**
     *
     * @param $name
     * @return UploadedFile|mixed|null
     */
    public function getUploadedFile($name)
    {
        return $this->files->get($name);
    }
    
    
    /**
     * Retrieve server parameters.
     *
     * Retrieves data related to the incoming request environment,
     * typically derived from PHP's $_SERVER superglobal. The data IS NOT
     * REQUIRED to originate from $_SERVER.
     *
     * @return array
     * @deprecated Use $this->server->all()
     */
    public function getServerParams()
    {
        return $this->server->all();
    }

    /**
     * @param $name
     * @param null $default
     * @return null
     * @deprecated Use $this->server->get($name)
     */
    public function getServerParam($name, $default = null)
    {
        return $this->server->get($name, $default);
    }
    
    /**
     * Retrieve attributes derived from the request.
     *
     * The request "attributes" may be used to allow injection of any
     * parameters derived from the request: e.g., the results of path
     * match operations; the results of decrypting cookies; the results of
     * deserializing non-form-encoded message bodies; etc. Attributes
     * will be application and request specific, and CAN be mutable.
     *
     * @return array Attributes derived from the request.
     * @deprecated Use $this->attributes->all()
     */
    public function getAttributes()
    {
        return $this->attributes->all();
    }

    /**
     * Retrieve a single derived request attribute.
     *
     * Retrieves a single derived request attribute as described in
     * getAttributes(). If the attribute has not been previously set, returns
     * the default value as provided.
     *
     * This method obviates the need for a hasAttribute() method, as it allows
     * specifying a default value to return if the attribute is not found.
     *
     * @see getAttributes()
     * @param string $name The attribute name.
     * @param mixed $default Default value to return if the attribute does not exist.
     * @return mixed
     * @deprecated Use $this->attributes->get($name)
     */
    public function getAttribute($name, $default = null)
    {
        return $this->attributes->get($name, $default);
    }

    /**
     * Set an attribute
     * 
     * @param $name
     * @param $value
     * @return $this
     * @deprecated Use $this->attributes->set($name, $value)
     */
    public function setAttribute($name, $value) 
    {
        $this->attributes->set($name, $value);
        return $this;
    }

    /**
     * Remove an attribute key from the list
     * 
     * @param $name
     * @return $this
     * @deprecated Use $this->attributes->remove($name)
     */
    public function removeAttribute($name)
    {
        $this->attributes->remove($name);
        return $this;
    }

    /**
     * Check if an attribute key exists
     * 
     * @param $name
     * @return bool
     * @deprecated Use $this->attributes->has($name)
     */
    public function hasAttribute($name)
    {
        return $this->attributes->has($name);
    }

    /**
     * Add a list of items to the attribute array
     *
     * @param array $items Key-value array of data to append to this collection
     * @return Request
     * @deprecated Use $this->attributes->replace($items)
     */
    public function replaceAttribute(array $items)
    {
        $this->attributes->replace($items);
        return $this;
    }

    
    /**
     * Get the client IP address.
     *
     * @return string|null IP address or null if none found.
     * @deprecated Use $this->attributes->getClientIp()
     */
    public function getIp()
    {
        return $this->getClientIp();
    }


    /**
     * Returns the referring \Tk\Uri if available.
     *
     * @return null|Uri Returns null if there was no referer.
     */
    public function getReferer()
    {
        $referer = $this->getServerParam('HTTP_REFERER');
        if ($referer) {
            $referer = Uri::create($referer);
        }
        return $referer;
    }

    /**
     * Check that this request came from our hosting server
     * as opposed to a remote request.
     *
     * @return bool
     */
    public function checkReferer()
    {
        $referer = \Tk\Uri::create($this->getReferer());
        $request = \Tk\Uri::create($this->getUri());
        if ($referer && $referer->getHost() == $request->getHost()) {
            return true;
        }
        return false;
    }


    /**
     * Get the browser userAgent string
     *
     * @return string
     */
    public function getUserAgent()
    {
        return $this->getServerParam('HTTP_USER_AGENT', '');
    }
    
    /**
     * Returns the raw post data.
     * 
     * note: In general, php://input should be used instead of $HTTP_RAW_POST_DATA.
     * @return string
     * @see http://php.net/manual/en/reserved.variables.httprawpostdata.php
     */
    public function getRawPostData()
    {
        return file_get_contents("php://input");
    }





    /**
     * Get collection iterator
     *
     * @return \ArrayIterator
     * @deprecated Use appropriate request parameter
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->request->all());
    }
    
}

/**
 * @todo Romvce this oneday when the request is completly symfony compatible.
 */
\Tk\Request::setFactory(function (array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null) {
    return new \Tk\Request($query, array_merge($query, $request), $attributes, $cookies, $files, $server, $content);
});