<?php
namespace Tk\DataMap\Form;

use Tk\DataMap\DataTypeInterface;

class Text extends DataTypeInterface
{

    public function getPropertyValue(array $array): ?string
    {
        $value = parent::getPropertyValue($array);
        if ($this->isNullable() && !is_string($value) && empty($value)) return null;
        return strval($value);
    }

    public function getColumnValue(object $object): string
    {
        return strval(parent::getColumnValue($object));
    }

}

