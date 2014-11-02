<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @author Darryl Ross <darryl.ross@aot.com.au>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk;

/**
 * A request object to send GET AND POST request to servers.
 *
 * Uses CURL.
 *
 *
 */
class HttpRequest
{

    private $options = array();
    private $headers = array();
    private $response = '';
    private $history = array();
    private $userAgent = '';
    private $timeout = 30;


    /**
     * HttpRequest constructor
     *
     * @param array $options (optional) an associative array with request options
     * @see http://au1.php.net/manual/en/function.curl-setopt.php
     */
    public function __construct(array $options = null)
    {
        if ($options) {
            $this->setOptions($options);
        }
        $this->userAgent = 'Mozilla/5.0 (compatible; HttpRequest 1.0) PHP/' . PHP_VERSION;
    }


    /**
     * Send a get request
     *
     * @param \Tk\Url|string $url
     * @return string
     * @throws Exception
     */
    public function get($url)
    {
        if (function_exists('curl_init')) {
            $ch = curl_init(\Tk\Url::create($url)->toString());
            if ($this->headers) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
            }
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

            foreach($this->options as $name => $value) {
                curl_setopt($ch, $name, $value);
            }
            $output = curl_exec($ch);
            if (curl_errno($ch)) {
                throw new \Tk\Exception(curl_error($ch));
            }
            curl_close($ch);
            $this->response = $this->history[] = $output;
        } else {
            // TODO Fix It! Fix It! Fix It! Fix It! Fix It! Fix It!
            // Try using fopen if curl was not available or did not work (could have been an SSL certificate issue)
            $opts = array('method' => 'POST',
                'content' => $data
            );

            if (!empty($header)) {
                $opts['header'] = $header;
            }
            $ctx = stream_context_create(array('http' => $opts));
            $fp = @fopen($url, 'rb', false, $ctx);
            if ($fp) {
                $output = @stream_get_contents($fp);
            }
        }
        return $output;
    }

    /**
     * Send A post request to a server
     *
     * @param \Tk\Url|string $url
     * @param string $data
     * @return string
     * @throws Exception
     */
    public function post($url, $data = '')
    {
        if (function_exists('curl_init')) {
            $ch = curl_init(\Tk\Url::create($url)->toString());
            if ($this->headers) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
            }
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_POST, 1);

            foreach($this->options as $name => $value) {
                curl_setopt($ch, $name, $value);
            }
            $output = curl_exec($ch);
            if (curl_errno($ch)) {
                throw new \Tk\Exception(curl_error($ch));
            }
            curl_close($ch);
        } else {
            // TODO Fix It! Fix It! Fix It! Fix It! Fix It! Fix It!
            // Try using fopen if curl was not available or did not work (could have been an SSL certificate issue)
            $opts = array('method' => 'POST',
                'content' => $data
            );

            if (!empty($header)) {
                $opts['header'] = $header;
            }
            $ctx = stream_context_create(array('http' => $opts));
            $fp = @fopen($url, 'rb', false, $ctx);
            if ($fp) {
                $output = @stream_get_contents($fp);
            }

        }

        $this->response = $this->history[] = $output;
        return $output;
    }





    /**
     * Get the response string if available.
     *
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set options
     * See the CURL docs for options types and values.
     *
     *
     * @param array $options (optional) an associative array, which values will overwrite the currently set request options;
     *                                  if empty or omitted, the options of the HttpRequest object will be reset
     * @return $this
     * @see http://au1.php.net/manual/en/function.curl-setopt.php
     */
    public function setOptions(array $options = null)
    {
        if ($options) {
            $this->options = $options;
        } else {
            $this->options = array();
        }
        return $this;
    }

    /**
     * Get options
     *
     * @return array an associative array containing currently set options.
     * @see http://au1.php.net/manual/en/function.curl-setopt.php
     */
    public function getOptions()
    {
        return $this->options;
    }


    /**
     * Add header
     * in the format of 'Content-type: text/plain'
     *
     * @param string $value
     * @return $this
     */
    public function addHeader($value)
    {
        $this->headers = $value;
        return $this;
    }


    /**
     * Add headers
     * in the format of:
     * array (
     *   'Content-type: text/plain',
     *   'Content-length: 100'
     * )
     *
     * @param array $headers an associative array as parameter containing additional header values
     * @return $this
     */
    public function addHeaders($headers)
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    /**
     * Get headers
     * in the format of:
     * array (
     *   'Content-type: text/plain',
     *   'Content-length: 100'
     * )
     *
     * @return array an associative array containing all currently set headers.
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Set headers
     * in the format of:
     * array (
     *   'Content-type: text/plain',
     *   'Content-length: 100'
     * )
     *
     * @param array $headers (optional) an associative array as parameter containing header values
     *                                  if empty or omitted, all previously set headers will be unset
     * @return $this
     */
    public function setHeaders(array $headers = null)
    {
        if ($headers) {
            $this->headers = $headers;
        } else {
            $this->headers = array();
        }
        return $this;
    }

    /**
     * Set the request timeout in seconds.
     * Default: 30sec
     *
     * @param integer $sec
     * @return $this
     */
    public function setTimeout($sec)
    {
        $this->timeout = $sec;
        return $this;
    }

    /**
     * Set the user agent string to be sent to the server.
     *
     * @param $string
     * @return $this
     */
    public function setUserAgent($string)
    {
        $this->userAgent = $string;
        return $this;
    }

}