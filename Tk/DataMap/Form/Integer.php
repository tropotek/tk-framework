<?php
namespace Tk\DataMap\Form;

use Tk\DataMap\DataTypeInterface;

class Integer extends DataTypeInterface
{

    public function getPropertyValue(array $array): int
    {
        $value = parent::getPropertyValue($array);
        return intval($value);
    }

    public function getColumnValue(object $object): string
    {
        $value = parent::getColumnValue($object);
        return strval($value);
    }

}

