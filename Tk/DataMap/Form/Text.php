<?php
namespace Tk\DataMap\Form;

use Tk\DataMap\DataTypeIface;

/**
 * map a string type from a form to an object property
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class Text extends DataTypeIface
{

    public function getKeyValue(array $array)
    {
        $value = parent::getKeyValue($array);
        if ($value !== null) {
            $value .= '';
        }
        return $value;
    }

    public function getPropertyValue(object $object)
    {
        $value = parent::getPropertyValue($object);
        if ($value !== null) {
            $value .= '';
        }
        return $value;
    }
    
}
