<?php
namespace Tt\DataMap\Db;

use Tt\DataMap\DataTypeInterface;

/**
 * map an integer type from a DB field to an object property
 */
class Integer extends DataTypeInterface
{

    public function getPropertyValue(array $array): mixed
    {
        $value = parent::getPropertyValue($array);
        if (!is_null($value)) $value = (int)$value;
        return $value;
    }

    public function getColumnValue(object $object): mixed
    {
        $value = parent::getColumnValue($object);
        if (!is_null($value)) $value = (int)$value;
        return $value;
    }
}