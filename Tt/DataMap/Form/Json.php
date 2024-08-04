<?php
namespace Tt\DataMap\Form;

use Tt\DataMap\DataTypeInterface;

/**
 * map a JSON string type from a form to an object property
 */
class Json extends DataTypeInterface
{

    public function getPropertyValue(array $array): mixed
    {
        $value = parent::getPropertyValue($array);
        if (!is_null($value)) $value = json_encode($value);
        return $value;
    }

    public function getColumnValue(object $object): mixed
    {
        $value = parent::getColumnValue($object);
        if (!is_null($value)) $value = json_decode($value);
        return $value;
    }
}

