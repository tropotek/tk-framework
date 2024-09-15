<?php
namespace Tk\DataMap\Form;

use Tk\DataMap\DataTypeInterface;

/**
 * map a percent type from a form to an object property
 * Values: 0.0 - 1.0
 */
class Percent extends DataTypeInterface
{

    public function getPropertyValue(array $array): float
    {
        $value = parent::getPropertyValue($array);
        if (!empty($value) && $value >= 1) $value = (float)($value/100);
        return (float)$value;
    }

    public function getColumnValue(object $object): string
    {
        $value = parent::getColumnValue($object);
        if ($value > 0 && $value < 1) $value = ($value*100);
        return strval($value);
    }

}

