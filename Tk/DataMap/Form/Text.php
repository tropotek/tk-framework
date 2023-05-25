<?php
namespace Tk\DataMap\Form;

use Tk\DataMap\DataTypeInterface;

/**
 * map a string type from a form to an object property
 */
class Text extends DataTypeInterface
{

    public function getKeyValue(array $array): mixed
    {
        $value = parent::getKeyValue($array);
        if (!($this->isNullable() && $value === null)) {
            $value .= '';
        }
        return $value;
    }

    public function getPropertyValue(object $object): mixed
    {
        $value = parent::getPropertyValue($object);
        if (!($this->isNullable() && $value === null)) {
            $value .= '';
        }
        return $value;
    }

}

