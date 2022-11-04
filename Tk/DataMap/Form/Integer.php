<?php
namespace Tk\DataMap\Form;

use Tk\DataMap\DataTypeInterface;

/**
 * map an integer type from a form to an object property
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class Integer extends DataTypeInterface
{

    public function getKeyValue(array $array): mixed
    {
        $value = parent::getKeyValue($array);
        if ($value !== null) $value = (int)$value;
        return $value;
    }

    public function getPropertyValue(object $object): mixed
    {
        $value = parent::getPropertyValue($object);
        if ($value !== null) $value .= '';
        return $value;
    }

}

