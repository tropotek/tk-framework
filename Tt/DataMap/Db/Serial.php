<?php
namespace Tt\DataMap\Db;

use Tt\DataMap\DataTypeInterface;

/**
 * map a serialized type from a DB field to an object property
 */
class Serial extends DataTypeInterface
{

    public function getPropertyValue(array $array): mixed
    {
        $value = parent::getPropertyValue($array);
        if (is_string($value)) {
            $value = unserialize(base64_decode($value));
        }
        return $value;
    }

    public function getColumnValue(object $object): mixed
    {
        $value = parent::getColumnValue($object);
        if (!is_null($value)) {
            $value = base64_encode(serialize($value));
        }
        return $value;
    }

}

