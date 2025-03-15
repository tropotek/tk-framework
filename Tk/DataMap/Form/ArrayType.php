<?php
namespace Tk\DataMap\Form;

use Tk\DataMap\DataTypeInterface;

/**
 * map an array type from a form field to an object property
 */
class ArrayType extends DataTypeInterface
{

    public function getPropertyValue(array $array): array
    {
        $value = parent::getPropertyValue($array);
        if (is_string($value) && str_contains($value, ',')) $value = explode(',', $value);
        if (!is_array($value)) $value = [$value];

        return $value;
    }

}

