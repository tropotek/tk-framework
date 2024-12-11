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

    public function getPropertyValue(array $array): mixed
    {
        $value = parent::getPropertyValue($array);
        if ($value instanceof \DateTime) {
            $value = new \DateTime($value->format('Y-m-d 00:00:00'), $value->getTimezone());
        }
        return $value;
    }
}