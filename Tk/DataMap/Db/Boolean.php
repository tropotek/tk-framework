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

        if (!(is_null($value) || is_bool($value))) {
            if ($value == $this->getColumn() || $value == $this->getProperty()) {
                $value = true;
            }

            if ($value == '') $value = false;

            $value = truefalse($value);
        }

        return $value;
    }

    public function getColumnValue(object $object): mixed
    {
        $value = parent::getColumnValue($object);

        if (!is_null($value)) {
            $value = (int)$value;
        }

        return $value;
    }

}