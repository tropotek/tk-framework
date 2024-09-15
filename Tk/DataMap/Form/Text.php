<?php
namespace Tk\DataMap\Form;

use Tk\DataMap\DataTypeInterface;

class Text extends DataTypeInterface
{

    public function getPropertyValue(array $array): string
    {
        return strval(parent::getPropertyValue($array));
    }

    public function getColumnValue(object $object): string
    {
        return strval(parent::getColumnValue($object));
    }

}

