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

    public function getColumnValue(object $object): string
    {
        $value = parent::getColumnValue($object);
        if (!is_null($value)) {
            $value = strval(intval($value));    // use 1 = true, 0 = false
        }
        return $value;
    }

}

