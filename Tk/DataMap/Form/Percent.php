<?php
namespace Tk\DataMap\Form;

use Tk\DataMap\DataTypeIface;

/**
 * map a percent type from a form to an object property
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class Percent extends DataTypeIface
{

    public function getKeyValue(array $array)
    {
        $value = parent::getKeyValue($array);
        if ($value !== null) $value = (float)($value/100);
        return $value;
    }

    public function getPropertyValue(object $object)
    {
        $value = parent::getPropertyValue($object);
        if ($value !== null) $value = ($value*100) . '';
        return $value;
    }
    
}

