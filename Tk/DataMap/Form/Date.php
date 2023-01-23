<?php
namespace Tk\DataMap\Form;

use Tk\DataMap\DataTypeInterface;


/**
 * map a Date type from a form to an object property
 *
 * @author Tropotek <http://www.tropotek.com/>
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

    public function getKeyValue(array $array): mixed
    {
        $value = parent::getKeyValue($array);
        if (!$value) $value = null;
        if ($value != null && !$value instanceof \DateTime) {
            $value = \Tk\Date::createFormDate($value, null, $this->format);
        }

        return $value;
    }

    public function getPropertyValue(object $object): mixed
    {
        $value = parent::getPropertyValue($object);
        if (!$value) $value = null;
        if ($value instanceof \DateTime) {
            return $value->format($this->format);
        }
        return $value;
    }

}

