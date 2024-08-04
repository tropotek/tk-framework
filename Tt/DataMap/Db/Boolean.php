<?php
namespace Tt\DataMap\Db;

use Tt\DataMap\DataTypeInterface;

/**
 * map a boolean type from a DB field to an object property
 */
class Boolean extends DataTypeInterface
{

    public function getPropertyValue(array $array): mixed
    {
        $value = parent::getPropertyValue($array);
        if (!(is_null($value) || is_bool($value))) {
            $value = (
                $value == $this->getColumn() ||
                $value == $this->getProperty() ||
                strtolower($value) == 'yes' ||
                strtolower($value) == 'true' ||
                (int)$value == 1
            );
        }
        return $value;
    }

    public function getColumnValue(object $object): mixed
    {
        $value = parent::getColumnValue($object);
        if (!is_null($value)) $value = (int)$value;
        return $value;
    }

}