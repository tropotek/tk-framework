<?php
namespace Tk\DataMap\Form;

use Tk\DataMap\DataTypeInterface;

/**
 * map a decimal type from a form to an object property
 */
class Decimal extends DataTypeInterface
{

    public function getPropertyValue(array $array): float
    {
        $value = parent::getPropertyValue($array);
        return (float)$value;
    }

    public function getColumnValue(object $object): string
    {
        $value = parent::getColumnValue($object);
        return strval($value);
    }

}

