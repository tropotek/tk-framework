<?php
namespace Tk\DataMap\Db;

use Tk\DataMap\DataTypeIface;

/**
 * map an integer type from a DB field to an object property
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class Integer extends DataTypeIface
{

    public function getKeyValue(array $array)
    {
        $value = parent::getKeyValue($array);
        if ($value !== null) $value = (int)$value;
        return $value;
    }

    public function getPropertyValue(object $object)
    {
        $value = parent::getPropertyValue($object);
        if ($value !== null) $value .= '';
        return $value;
    }
    
}

