<?php
namespace Tk\DataMap\Form;

use Tk\DataMap\DataTypeIface;

/**
 * map a JSON string type from a form to an object property
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class Json extends DataTypeIface
{

    public function getKeyValue(array $array)
    {
        $value = parent::getKeyValue($array);
        if ($value) {
            $value = json_encode($value);
        }
        return $value;
    }

    public function getPropertyValue(object $object)
    {
        $value = parent::getPropertyValue($object);
        if ($value) {
            $value = json_decode($value);
        }
        return $value;
    }
}

