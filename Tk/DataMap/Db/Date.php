<?php
namespace Tk\DataMap\Db;

use Tk\DataMap\DataTypeInterface;

/**
 * map a Date type from a DB field to an object property
 */
class Date extends DataTypeInterface
{

    protected string $format = 'Y-m-d H:i:s';

    public function __construct(string $property, string $key = '')
    {
        parent::__construct($property, $key);
        $this->format = \Tk\Date::FORMAT_ISO_DATETIME;
    }

    public function setDateFormat(string $format): Date
    {
        $this->format = $format;
        return $this;
    }

    public function getKeyValue(array $array): mixed
    {
        $value = parent::getKeyValue($array);
        // This date is assumed as null
        if ($value == '0000-00-00 00:00:00') {
            $value = 'now';
            if ($this->isNullable()) {
                $value = null;
            }
        }
        if ($value != null) {
            $value = \Tk\Date::create($value);
        }
        return $value;
    }

    public function getPropertyValue(object $object): mixed
    {
        $value = parent::getPropertyValue($object);
        if ($value instanceof \DateTime) {
            return $value->format($this->format);
        }
        return $value;
    }

}

