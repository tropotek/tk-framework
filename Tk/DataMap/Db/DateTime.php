<?php
namespace Tk\DataMap\Db;

use Tk\Exception;
use Tk\DataMap\DataTypeInterface;

/**
 * map a datetime/timestamp from a DB field to a \DateTime object
 */
class DateTime extends DataTypeInterface
{
    private string $format      = 'Y-m-d H:i:s';
    private string $timezone    = '';
    private bool   $isImmutable = false;


    public function __construct(string $property, string $key = '')
    {
        parent::__construct($property, $key);
        $this->format = \Tk\Date::FORMAT_ISO_DATETIME;
        $this->timezone = date_default_timezone_get();
    }

    public function getPropertyValue(array $array): mixed
    {
        $value = parent::getPropertyValue($array);
        if ($this->isNullable() && (empty($value) || !is_string($value))) return null;

        if ($this->isImmutable) {
            $value = \DateTimeImmutable::createFromFormat($this->format, trim($value), $this->getTimeZone());
            if ($value === false) {
                throw new Exception(implode(", ", (\DateTimeImmutable::getLastErrors()['errors'] ?? ['Unknown Date Error'])) .
                    " for date: '$value'");
            }
        } else {
            $value = \DateTime::createFromFormat($this->format, trim($value), $this->getTimeZone());
            if ($value === false) {
                throw new Exception(implode(", ", (\DateTime::getLastErrors()['errors'] ?? ['Unknown Date Error'])) .
                    " for date: '$value'");
            }
        }

        return $value;
    }

    public function getColumnValue(object $object): mixed
    {
        $value = parent::getColumnValue($object);
        if ($value instanceof \DateTimeInterface) {
            $value = $value->format($this->format);
        }
        if ($this->isNullable() && (empty($value) || !is_string($value))) return null;
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

    public function setImmutable(bool $isImmutable): DateTime
    {
        $this->isImmutable = $isImmutable;
        return $this;
    }

    public function getTimeZone(): ?\DateTimeZone
    {
        if (empty($this->timezone)) return null;
        return new \DateTimeZone($this->timezone);
    }

}