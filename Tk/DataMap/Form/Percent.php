<?php
namespace Tk\DataMap\Form;

use Tk\DataMap\DataTypeInterface;

/**
 * map a percent type from a form to an object property
 */
class Percent extends DataTypeInterface
{

    public function getPropertyValue(array $array): mixed
    {
        $value = parent::getPropertyValue($array);
        if ($value !== null) $value = (float)($value/100);
        return $value;
    }

    public function getColumnValue(object $object): mixed
    {
        $value = parent::getColumnValue($object);
        if ($value !== null) $value = ($value*100) . '';
        return $value;
    }

}

