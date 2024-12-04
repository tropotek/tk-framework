<?php
namespace Tk\DataMap\Form;

use Tk\DataMap\DataTypeInterface;

/**
 * map a boolean type from a form to an object property
 */
class Boolean extends DataTypeInterface
{

    public function getPropertyValue(array $array): bool
    {
        $value = parent::getPropertyValue($array);
        if (!(is_null($value) || is_bool($value))) {
            if ($value == $this->getColumn() || $value == $this->getProperty()) {
                $value = true;
            } else {
                $value = truefalse($value);
            }
        }
        return $value;
    }

    public function getColumnValue(object $object): string
    {
        $value = parent::getColumnValue($object);
        if (!is_null($value)) {
            $value = strval(intval($value));    // use 1 = true, 0 = false
        }
        return $value;
    }

}

