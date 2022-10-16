<?php
namespace Tk\DataMap\Form;

use Tk\DataMap\DataTypeIface;

/**
 * map an array type from a form field to an object property
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class ArrayType extends DataTypeIface
{

    public function getKeyValue(array $array)
    {
        $value = parent::getKeyValue($array);
        $value = explode(',', $value);
        return $value;
    }

    public function getPropertyValue(object $object)
    {
        $value = parent::getPropertyValue($object);
        if ($value !== null) {
            $value = implode(',', $value);
        }
        return $value;
    }
    
}

