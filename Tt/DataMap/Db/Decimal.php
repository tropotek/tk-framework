<?php
namespace Tt\DataMap\Db;

use Tt\DataMap\DataTypeInterface;

/**
 * map a decimal/float type from a DB field to an object property
 */
class Decimal extends DataTypeInterface
{

    public function getPropertyValue(array $array): mixed
    {
        $value = parent::getPropertyValue($array);
        if (!is_null($value)) $value = (float)$value;
        return $value;
    }

    public function getColumnValue(object $object): mixed
    {
        $value = parent::getColumnValue($object);
        if (!is_null($value)) $value = (float)$value;
        return $value;
    }

}