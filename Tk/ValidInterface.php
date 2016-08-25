<?php
namespace Tk;

/**
 * Class ValidInterface
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
interface ValidInterface
{
    /**
     * Validate an email
     * @match name@domain.com, name-name@domain.com
     * @no-match name@domain, name@domain.com
     */
    const REG_EMAIL = '/^[0-9a-zA-Z\-\._]*@[0-9a-zA-Z\-]([-.]?[0-9a-zA-Z])*$/';

    /**
     * Validate an email
     *
     * @match regexlib.com | this.is.a.museum | 3com.com
     * @no-match notadomain-.com | helloworld.c | .oops.org
     */
    const REG_DOMAIN = '/^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}$/';

    /**
     * Check http/https urls with this
     * @match http://www.domain.com
     * @no-match www.domain.com
     */
    const REG_URL = '/^[a-z0-9]{2,8}:\/\/(www\.)?[\S]+$/i';

    /**
     * IP V4 check
     *
     * @match 255.255.255.255
     * @no-match domain.com, 233.233.233.0/24
     */
    const REG_IPV4 = '/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/';

    /**
     * Extract flash video urls with this expresion
     */
    const REG_FLASH_VIDEO = '/<embed[^>]*src=\"?([^\"]*)\"?([^>]*alt=\"?([^\"]*)\"?)?[^>]*>/i';

    /**
     * Validate a username
     *
     * @match Name, name@domain.com
     * @no-match *username
     */
    const REG_USERNAME = '/^[a-zA-Z0-9_@ \.\-]{3,64}$/i';

    /**
     * Validate a password
     *
     * @match Name, name@domain.com
     * @no-match *username
     */
    const REG_PASSWORD = '/^.{6,64}$/i';



    /**
     * Validate this object's current state and return an array
     * with error messages. This will be useful for validating
     * objects for use within forms.
     *
     * @return array
     */
    public function validate();


}