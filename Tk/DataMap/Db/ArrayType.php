<?php
namespace Tk\DataMap\Db;

use Tk\DataMap\DataTypeInterface;

/**
 * map an array type from a DB field to an object property
 */
class ArrayType extends DataTypeInterface
{

    public function getKeyValue(array $array): mixed
    {
        $value = parent::getKeyValue($array);
        $value = explode(',', $value);
        return $value;
    }

    public function getPropertyValue(object $object): mixed
    {
        $value = parent::getPropertyValue($object);
        if ($value !== null) {
            $value = implode(',', $value);
        }
        return $value;
    }

}

