<?php
namespace Tk\DataMap\Db;

/**
 * map a Date from a DB field to a \DateTime object
 */
class Date extends DateTime
{
    public function __construct(string $property, string $key = '')
    {
        parent::__construct($property, $key);
        $this->format = \Tk\Date::FORMAT_ISO_DATE;
    }
}