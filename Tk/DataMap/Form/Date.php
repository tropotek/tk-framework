<?php
namespace Tk\DataMap\Form;

use Tk\DataMap\DataTypeInterface;

/**
 * map a Date type from a form to an object property
 */
class Date extends DataTypeInterface
{
    /**
     * The date format received from the array/form
     * @default 'd/m/Y' => `31/12/2000`
     */
    protected string $format = 'd/m/Y';


    public function __construct(string $property)
    {
        parent::__construct($property);
        $this->format = \Tk\Date::$FORM_FORMAT;
    }


    public function setDateFormat(string $format): Date
    {
        $this->format = $format;
        return $this;
    }

    public function getPropertyValue(array $array): mixed
    {
        $value = parent::getPropertyValue($array);
        if (!(empty($value) || $value instanceof \DateTime)) {
            $value = \Tk\Date::createFormDate($value, null, $this->format);
        }
        if (empty($value)) $value = null;
        return $value;
    }

    public function getColumnValue(object $object): mixed
    {
        $value = parent::getColumnValue($object);
        if ($value instanceof \DateTime) {
            return $value->format($this->format);
        }
        return $value;
    }

}

