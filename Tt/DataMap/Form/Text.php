<?php
namespace Tt\DataMap\Form;

use Tt\DataMap\DataTypeInterface;

/**
 * map a string type from a form to an object property
 */
class Text extends DataTypeInterface
{

    public function getPropertyValue(array $array): mixed
    {
        $value = parent::getPropertyValue($array);
        if (!is_null($value)) $value = strval($value);
        return $value;
    }

    public function getColumnValue(object $object): mixed
    {
        $value = parent::getColumnValue($object);
        if (!is_null($value)) $value = strval($value);
        return $value;
    }

}

