<?php
namespace Tk\DataMap\Form;

use Tk\DataMap\DataTypeInterface;

class Date extends DataTypeInterface
{

    /**
     * The date format received from the array/form
     * @default 'd/m/Y' => `31/12/2000`
     */
    protected string $format = '';
    protected ?\DateTimeZone $timezone = null;


    public function __construct(string $property)
    {
        parent::__construct($property);
    }

    public function setDateFormat(string $format): self
    {
        $this->format = $format;
        return $this;
    }

    public function setTimezone(string $tz): self
    {
        $this->timezone = new \DateTimeZone($tz);
        return $this;
    }

    public function getPropertyValue(array $array): ?\DateTime
    {
        $value = parent::getPropertyValue($array);
        if ($this->isNullable() && empty($value)) return null;
        if (!($value instanceof \DateTime)) {
            $value = new \DateTime($value, $this->timezone);
        }
        return $value;
    }

    public function getColumnValue(object $object): string
    {
        $value = parent::getColumnValue($object);
        if ($value instanceof \DateTime) {
            if ($this->format) {
                return $value->format($this->format);
            } elseif (str_ends_with($this->getProperty(), 'At')) {
                return $value->format( 'Y-m-d H:i');
            }
            return $value->format( \Tk\Date::FORMAT_ISO_DATE);
        }
        return strval($value);
    }

}

