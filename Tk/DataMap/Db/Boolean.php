<?php
namespace Tk\DataMap\Db;

use Tk\DataMap\DataTypeInterface;

/**
 * map a boolean type from a DB field to an object property
 */
class Boolean extends DataTypeInterface
{

    public function getKeyValue(array $array): mixed
    {
        $value = parent::getKeyValue($array);
        if ($value !== null) {
            if ($value == $this->getKey() || strtolower($value) == 'yes' || strtolower($value) == 'true' || ((int)$value)) {
                return true;
            } else {
                return false;
            }
        }
        return $value;
    }

    public function getPropertyValue(object $object): mixed
    {
        $value = parent::getPropertyValue($object);
        if ($value !== null) {
            $value = (int)$value;
        }
        return $value;
    }

}

