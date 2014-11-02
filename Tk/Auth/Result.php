<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Auth;

/**
 * Auth result object
 *
 * @package Auth
 */
class Result
{
    /**
     * General Failure
     */
    const FAILURE                        =  0;

    /**
     * Failure due to identity not being found.
     */
    const FAILURE_IDENTITY_NOT_FOUND     = -1;

    /**
     * Failure due to identity being ambiguous.
     */
    const FAILURE_IDENTITY_AMBIGUOUS     = -2;

    /**
     * Failure due to invalid credential being supplied.
     */
    const FAILURE_CREDENTIAL_INVALID     = -3;

    /**
     * Failure due to uncategorized reasons.
     */
    const FAILURE_UNCATEGORIZED          = -4;

    /**
     * Authentication success.
     */
    const SUCCESS                        =  1;


    /**
     * Authentication result code
     *
     * @var int
     */
    protected $code = 0;

    /**
     * The identity used in the authentication attempt
     *
     * @var mixed
     */
    protected $identity = null;

    /**
     * An array of string reasons why the authentication attempt was unsuccessful
     * If authentication was successful, this should be an empty array.
     *
     * @var array
     */
    protected $messages = array();



    /**
     * Sets the result code, identity, and failure messages
     *
     * @param  int     $code
     * @param  mixed   $identity
     * @param  array   $messages
     */
    public function __construct($code, $identity, $messages = array())
    {
        $this->code     = $code;
        $this->identity = $identity;
        $this->messages = $messages;
    }

    /**
     * Helper method for result object
     *
     * @param  int     $code
     * @param  mixed   $identity
     * @param  array   $messages
     * @return \Tk\Auth\Result
     */
    static function create($code, $identity = '', $messages = array())
    {
        if (!is_array($messages)) $messages = array($messages);
        $obj = new self($code, $identity, $messages);
        return $obj;
    }

    /**
     * Returns whether the result represents a successful authentication attempt
     *
     * @return bool
     */
    public function isValid()
    {
        return ($this->code > 0) ? true : false;
    }

    /**
     * getCode() - Get the result code for this authentication attempt
     *
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Returns the identity used in the authentication attempt
     *
     * @return string
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * Returns an array of string reasons why the authentication attempt was unsuccessful
     * If authentication was successful, this method returns an empty array.
     *
     * @return array
     */
    public function getMessages()
    {
        if (!is_array($this->messages)) {
            $this->messages = array('messages' => $this->messages);
        }
        return $this->messages;
    }
}
