<?php
namespace Tt\DataMap\Db;

use Tt\DataMap\DataTypeInterface;

/**
 * map an integer type from a DB field to an object property
 */
class Integer extends DataTypeInterface
{

    public function getKeyValue(array $array): mixed
    {
        $value = parent::getKeyValue($array);
        if ($value !== null) $value = (int)$value;
        return $value;
    }

    public function getPropertyValue(object $object): mixed
    {
        $value = parent::getPropertyValue($object);
        if ($value !== null) $value = (int)$value;
//        if ($this->isNullable()) {
//            if (!$value) $value = null;
//        } else {
//            if (!$value) $value = '0';
//        }
        return $value;
    }
}
