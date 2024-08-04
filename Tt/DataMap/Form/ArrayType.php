<?php
namespace Tt\DataMap\Form;

use Tt\DataMap\DataTypeInterface;

/**
 * map an array type from a form field to an object property
 */
class ArrayType extends DataTypeInterface
{

    public function getPropertyValue(array $array): mixed
    {
        $value = parent::getPropertyValue($array);
        if (is_string($value)) $value = explode(',', $value);
        return $value;
    }

    public function getColumnValue(object $object): mixed
    {
        $value = parent::getColumnValue($object);
        if (is_array($value)) $value = implode(',', $value);
        return $value;
    }

}

