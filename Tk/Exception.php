<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk;

/**
 * Exception
 *
 */
class Exception extends \Exception
{

    /**
     * @var string
     */
    protected $dump = '';


    /**
     * Set any memory, code dump data to display in the exception error
     *
     * @param string $dump
     */
    public function setDump($dump)
    {
        $this->dump = $dump;
    }


    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of the exception
     * @link http://php.net/manual/en/exception.tostring.php
     * @return string the string representation of the exception.
     */
    public function __toString()
    {
        $str = parent::__toString();
        if ($this->dump != null) {
            $str .= $this->dump . "\n\n";
        }
        return $str;
    }

}
