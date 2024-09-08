<?php
namespace Tk\DataMap\Db;

/**
 * map a year from a DB field to a \DateTime object
 */
class Year extends DateTime
{
    public function __construct(string $property, string $key = '')
    {
        parent::__construct($property, $key);
        $this->format = 'Y';
    }

    public function getPropertyValue(array $array): mixed
    {
        $value = null;
        if (array_key_exists($this->getColumn(), $array)) {
            $value = $array[$this->getColumn()];
        }
        if (is_numeric($value) && strlen($value) == 4) {
            $value = \Tk\Date::create("$value-01-01");
        }
        return $value;
    }
}