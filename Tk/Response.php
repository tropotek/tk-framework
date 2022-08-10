<?php
namespace Tk;

/**
 * @author Michael Mifsud <http://www.tropotek.com/>
 * @see http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 * @see https://github.com/symfony/http-foundation/blob/master/Response.php
 */
class Response extends \Symfony\Component\HttpFoundation\Response
{

    /**
     * Set a header in the message
     *
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setHeader($name, $value)
    {
        $this->headers->set($name, $value);
        return $this;
    }

    /**
     * This method appends a header value. Unlike the set() method,
     * this method _appends_ this new value to any values
     * that already existed for this header name.
     *
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function addHeader($name, $value)
    {
        $this->headers->set($name, $value);
        return $this;
    }

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name Case-insensitive header field name.
     * @return bool Returns true if any header names match the given header
     *     name using a case-insensitive string comparison. Returns false if
     *     no matching header name is found in the message.
     */
    public function hasHeader($name)
    {
        return $this->headers->has($name);
    }

    /**
     * Retrieves a message header value by the given case-insensitive name.
     *
     * This method returns an array of all the header values of the given
     * case-insensitive header name.
     *
     * If the header does not appear in the message, this method MUST return an
     * empty array.
     *
     * @param string $name Case-insensitive header field name.
     * @return string[] An array of string values as provided for the given
     *    header. If the header does not appear in the message, this method MUST
     *    return an empty array.
     */
    public function getHeader($name)
    {
        return $this->headers->get($name, []);
    }

}