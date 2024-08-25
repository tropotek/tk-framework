<?php
namespace Tt\DataMap\Db;

use Tk\Exception;
use Tt\DataMap\DataTypeInterface;

/**
 * map a datetime/timestamp from a DB field to a \DateTime object
 */
class DateTime extends DataTypeInterface
{
    protected string $format   = 'Y-m-d H:i:s';
    protected string $timezone = '';

    public function __construct(string $property, string $key = '')
    {
        parent::__construct($property, $key);
        $this->format = \Tk\Date::FORMAT_ISO_DATETIME;
        $this->timezone = date_default_timezone_get();
    }

    public function getPropertyValue(array $array): mixed
    {
        $value = trim(parent::getPropertyValue($array));
        if (is_string($value)) {
            $v = \DateTime::createFromFormat($this->format, $value, $this->getTimeZone());
            if ($v === false) throw new Exception(implode(", ", (\DateTime::getLastErrors()['errors'] ?? ['Unknown Date Error'])) .
                " for date: '$value'");
            $value = $v;
        }
        return $value;
    }

    public function getColumnValue(object $object): mixed
    {
        $value = parent::getColumnValue($object);
        if ($value instanceof \DateTime) {
            $value = $value->format($this->format);
        }
        return $value;
    }

    public function setDateFormat(string $format): DateTime
    {
        $this->format = $format;
        return $this;
    }

    public function setTimezone(string $timezone): DateTime
    {
        $this->timezone = $timezone;
        return $this;
    }

    public function getTimeZone(): ?\DateTimeZone
    {
        if (empty($this->timezone)) return null;
        return new \DateTimeZone($this->timezone);
    }

}