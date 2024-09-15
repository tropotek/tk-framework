<?php
namespace Tk\DataMap\Form;

use Tk\DataMap\DataTypeInterface;

/**
 * map an array type from a form field to an object property
 *
 * @todo See if we still need this, not sure we will ever pass a comma seperated list, but not impossible.
 * @deprecated
 */
class ArrayType extends DataTypeInterface
{

    public function getPropertyValue(array $array): array
    {
        $value = parent::getPropertyValue($array);
        if (is_string($value)) $value = explode(',', $value);
        return $value;
    }

    public function getColumnValue(object $object): string
    {
        $value = parent::getColumnValue($object);
        if (is_array($value)) $value = implode(',', $value);
        return $value;
    }

}

