<?php
namespace Tk\DataMap\Db;

/**
 * map a time from a DB field to a \DateTime object
 */
class Time extends DateTime
{
    public function __construct(string $property, string $key = '')
    {
        parent::__construct($property, $key);
        $this->setDateFormat(\Tk\Date::FORMAT_ISO_TIME);
    }
}