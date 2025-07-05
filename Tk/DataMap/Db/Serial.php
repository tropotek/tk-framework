<?php
namespace Tk\DataMap\Db;

use Tk\DataMap\DataTypeInterface;

/**
 * map a serialized type from a DB field to an object property
 */
class Serial extends DataTypeInterface
{

    public function getPropertyValue(array $array): mixed
    {
        $value = parent::getPropertyValue($array);
        if ($this->isNullable() && !is_string($value)) return null;
        return unserialize(base64_decode($value));
    }

    public function getColumnValue(object $object): mixed
    {
        $value = parent::getColumnValue($object);
        if ($this->isNullable() && is_null($value)) return null;
        return base64_encode(serialize($value));
    }

}

