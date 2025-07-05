<?php
namespace Tk\DataMap\Db;

use Tk\DataMap\DataTypeInterface;

/**
 * map a boolean type from a DB field to an object property
 */
class Boolean extends DataTypeInterface
{

    public function getPropertyValue(array $array): mixed
    {
        $value = parent::getPropertyValue($array);
        if ($this->isNullable() && !is_bool($value)) return null;

        if ($value == $this->getColumn() || $value == $this->getProperty()) {
            $value = true;
        }
        if ($value == '') $value = false;
        return $value;
    }

    public function getColumnValue(object $object): mixed
    {
        $value = parent::getColumnValue($object);
        if ($this->isNullable() && !is_bool($value)) return null;
        return (int)$value;
    }

}