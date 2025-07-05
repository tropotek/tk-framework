<?php
namespace Tk\DataMap\Db;

use Tk\DataMap\DataTypeInterface;

/**
 * map a decimal/float type from a DB field to an object property
 */
class Decimal extends DataTypeInterface
{

    public function getPropertyValue(array $array): mixed
    {
        $value = parent::getPropertyValue($array);
        if ($this->isNullable() && empty($value)) return null;
        return (float)$value;
    }

    public function getColumnValue(object $object): mixed
    {
        $value = parent::getColumnValue($object);
        if ($this->isNullable() && empty($value)) return null;
        return (float)$value;
    }

}