<?php
namespace Tk\DataMap\Db;

use Tk\DataMap\DataTypeInterface;

/**
 * map a decimal/float type from a DB field to an object property
 */
class Decimal extends DataTypeInterface
{

    public function getKeyValue(array $array): mixed
    {
        $value = parent::getKeyValue($array);
        if ($value !== null) $value = (float)$value;
        return $value;
    }

    public function getPropertyValue(object $object): mixed
    {
        $value = parent::getPropertyValue($object);
        if ($value !== null) $value .= '';
        return $value;
    }

}

