<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk;

use \Tk\Log\Log;

/**
 * An OO Wrapper around a HTTP response.
 *
 * @package Tk
 */
class Response extends Object
{
    const TYPE_HTML = 'html';
    const TYPE_XML = 'xml';
    const TYPE_JSON = 'json';
    const TYPE_TEXT = 'text';
    const TYPE_OTHER = '';

    /**
     * Status codes
     * @var int
     */
    const SC_CONTINUE = 100;
    const SC_SWITCHING_PROTOCOLS = 101;
    const SC_OK = 200;
    const SC_CREATED = 201;
    const SC_ACCEPTED = 202;
    const SC_NON_AUTHORATIVE_INFO = 203;
    const SC_NO_CONTENT = 204;
    const SC_RESET_CONTENT = 205;
    const SC_PARTIAL_CONTENT = 206;
    const SC_MULTIPLE_CHOICES = 300;
    const SC_MOVED_PERMANENTLY = 301;
    const SC_FOUND = 302;
    const SC_SEE_OTHER = 303;
    const SC_NOT_MODIFIED = 304;
    const SC_USE_PROXY = 305;
    const SC_TEMP_REDIRECT = 307;
    const SC_BAD_REQUEST = 400;
    const SC_UNAUTHORIZED = 401;
    const SC_PAYMENT_REQUIRED = 402;
    const SC_FORBIDDEN = 403;
    const SC_NOT_FOUND = 404;
    const SC_METHOD_NOT_ALLOWED = 405;
    const SC_NOT_ACCEPTABLE = 406;
    const SC_PROXY_AUTH_REQUIRED = 407;
    const SC_REQUEST_TIMEOUT = 408;
    const SC_CONFLICT = 409;
    const SC_GONE = 410;
    const SC_LENGTH_REQUIRED = 411;
    const SC_PRECONDITION_FAILED = 412;
    const SC_REQUEST_ENTITY_TO_LARGE = 413;
    const SC_REQUEST_URI_TO_LONG = 414;
    const SC_UNSUPORTED_MEDIA_TYPE = 415;
    const SC_REQUEST_RANGE_NOT_STATISFIABLE = 416;
    const SC_EXPECTATION_FAILED = 417;
    const SC_INTERNAL_SERVER_ERROR = 500;
    const SC_NOT_IMPLEMENTED = 501;
    const SC_BAD_GATEWAY = 502;
    const SC_SERVICE_UNAVAILABLE = 503;
    const SC_GATEWAY_TIMEOUT = 504;
    const SC_HTTP_VERSION_NOT_SUPPORTED = 505;

    /**
     * Server Status codes
     * @var array
     */
	static public $statusText = Array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported'
    );

    /**
     * @var string
     */
    protected $errorTemplate = '<html>
  <head>
    <title>{statusCode} {title}</title>
    <style type="text/css">
      body {
        margin: 0;
        padding: 0;
      }
      h1 {
       margin: 0px;
       padding: 4px 10px;
       background: #369;
       color: #FFF;
      }
      p {
       margin: 10px 10px;
       width: 60%;
       min-width: 300px;
      }
      pre {
        color: #000;
        background-color: #EFEFEF;
        border: 1px dashed #CCC;
        padding: 10px;
        margin: 10px 10px 10px 40px;
        font-size: 12px;
      }
      .content {
        padding: 0px 10px;
      }
    </style>
  </head>
  <body>
    <h1>{title}</h1>
    <div class="content">
    <p>
      {message}
    </p>

    {dump}

    </div>
  </body>
</html>';

    /**
     * @var string
     */
    protected $buffer = '';

    /**
     * @var bool
     */
    protected $committed = false;

    /**
     * @var array
     */
    protected $headers = array();

    protected $type = self::TYPE_OTHER;


    /**
     * Create a Response object
     *
     * @param string $buffer
     */
    public function __construct($buffer = '')
    {
        $this->buffer = $buffer;

    }

    /**
     * Create a Plain Text response object
     *
     * @param string $buffer
     * @return \Tk\Response
     */
    static function createTextResponse($buffer = '')
    {
        $obj = new self($buffer);
        $obj->addHeader('Content-Type', 'text; charset=utf-8');
        $obj->type = self::TYPE_HTML;
        return $obj;
    }
    /**
     * Create a JSON response object
     *
     * @param string $buffer
     * @return \Tk\Response
     */
    static function createJsonResponse($buffer = '')
    {
        //if (!is_array(json_decode($buffer))) {
        if (!json_decode($buffer)) {
            $buffer = json_encode($buffer);
        }
        $obj = new self($buffer);
        $obj->addHeader('Content-Type', 'application/json; charset=utf-8');
        $obj->addHeader('Cache-Control', 'no-cache, must-revalidate');
        $obj->addHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
        $obj->type = self::TYPE_JSON;
        return $obj;
    }
    /**
     * Create a XML response object
     *
     * @param string $buffer
     * @return \Tk\Response
     */
    static function createXmlResponse($buffer = '')
    {
        $obj = new self($buffer);
        $obj->addHeader('Content-Type', 'text/xml; charset=utf-8');
        $obj->addHeader('Cache-Control', 'no-cache, must-revalidate');
        $obj->addHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
        $obj->type = self::TYPE_XML;
        return $obj;
    }
    /**
     * Create a XML response object
     *
     * @param string $buffer
     * @return \Tk\Response
     */
    static function create($buffer = '')
    {
        $obj = new self($buffer);
        return $obj;
    }


    /**
     * Add a header value to send with the response.
     *
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function addHeader($name, $value = '')
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Get the current headers array.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     *  Forces any content in the buffer to be written to the client.
     *
     * A call to this method automatically commits the response, meaning the
     * status code and headers will be written.
     *
     */
    public function flush()
    {
        $this->notify('preFlush');
        $this->committed = true;
        $this->flushHeaders();
        // Check this for XML response
        if ($this->buffer[0] == '<') {
            $this->buffer = str_replace(array('<?xml version="1.0"?>' . "\n"), "", $this->buffer);
        }

        $this->notify('postFlush');
        echo $this->buffer;
    }

    /**
     * Write any headers to the buffer
     *
     */
    public function flushHeaders()
    {
        $this->notify('preFlushHeaders');
        foreach ($this->headers as $name => $value) {
            $str = '';
            if (strtolower($name) == 'status') {
                $str = $_SERVER['SERVER_PROTOCOL'] . ' ' . $value . self::$statusText[$value];
            } else if ($value === '') {
                $str = $name;
            } else {
                $str ="$name: $value";
            }
            //Log::write('Header Added:  ' . $str);
            header($str);
        }
        $this->headers = array();
        $this->notify('postFlushHeaders');
    }

    /**
     * Returns a boolean indicating if the response has been committed.
     *
     * A committed response has already had its status code and headers written.
     * @return bool
     */
    public function isCommitted()
    {
        return $this->committed;
    }

    /**
     * Clears any data that exists in the buffer.
     *
     * @param bool $keepHeaders
     * @throws \Tk\Exception
     */
    public function reset($keepHeaders = false)
    {
        if ($this->committed) {
            throw new Exception('1000: The response has already been committed.');
        }
        $this->buffer = '';
        if (!$keepHeaders) {
            $this->headers = array();
        }
    }

    /**
     * Writes to the response buffer.
     *
     * @param $data
     * @throws Exception
     */
    public function write($data)
    {
        $this->notify('preWrite');
        if ($this->committed) {
            throw new Exception('1002: The response has already been committed.');
        }
        $this->buffer .= $data;
        $this->notify('postWrite');
    }

    /**
     * Get the buffer string
     *
     * @return string
     */
    public function getBuffer()
    {
        return $this->buffer;
    }

    /**
     * Returns a textual representation of the object.
     * Alias for getBuffer()
     * @return string
     */
    public function toString()
    {
        return $this->getBuffer();
    }


    /**
     * Sends an error response to the client using the specified status.
     *
     * The response will look like an HTML-formatted server error page
     * containing the specified message, The the content type will be set to
     * "text/html", and cookies and other headers will be left unmodified.
     *
     * If the response has already been committed, this method throws an
     * IllegalStateException. After using this method, the response should be
     * considered to be committed and should not be written to.
     *
     * @param string $msg An optional message
     * @param int|\Tk\const $statusCode The error status code
     * @param string $dump An optional descriptive string
     *
     * @throws Exception
     */
    public function sendError($msg = '', $statusCode = 500, $dump = '')
    {
        $disCode = $statusCode;
        if (!in_array($statusCode, $this->getConstantList('SC'))) {
            $statusCode = self::SC_SERVICE_UNAVAILABLE;
        }
        if ($this->committed) {
            throw new Exception('1001: The response has already been committed.');
        }
        $this->reset();
        $this->addHeader(self::$statusText[$statusCode], $statusCode);
        $errorHtml = $this->getErrorTemplate($msg, $statusCode, $dump);

//        $this->write(sprintf($this->errorTemplate, $statusCode, self::$statusText[$statusCode],
//                $disCode . ' ' . self::$statusText[$statusCode], $msg, $dump));

        $this->write($errorHtml);
        $this->committed = true;
        $this->flush();
        exit();
    }

    /**
     * Generate a html template to show the error
     *
     * @param string $msg
     * @param int $statusCode
     * @param string $dump
     * @return mixed|string
     */
    protected function getErrorTemplate($msg = '', $statusCode = 500, $dump = '')
    {
        $html = $this->errorTemplate;

        $file = $this->getConfig()->get('system.theme.selected.path') . '/error.tpl';
        if (is_file($file)) {
            $html = file_get_contents($file);
        }
        if ($dump) {
            $dump = '<pre class="error-dump">'.$dump.'</pre>';
        }

        $list = array(
            '{siteUrl}' => $this->getConfig()->getSiteUrl(),
            '{siteTitle}' => $this->getConfig()->getSiteTitle(),
            '{themeUrl}' => $this->getConfig()->get('system.theme.selected.url'),
            '{title}' => self::$statusText[$statusCode],
            '{message}' => $msg,
            '{dump}' => $dump,
            '{statusCode}' => $statusCode
        );

        foreach($list as $k => $v) {
            $html = str_replace($k, $v, $html);
        }

        return $html;
    }

}