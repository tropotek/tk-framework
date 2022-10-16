<?php
namespace Tk\DataMap\Db;

use Tk\DataMap\DataTypeIface;

/**
 * map a serialized type from a DB field to an object property
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class Serial extends DataTypeIface
{

    public function getKeyValue(array $array)
    {
        $value = parent::getKeyValue($array);
        if ($value) {
            $value = unserialize(base64_decode($value));
        }
        return $value;
    }

    public function getPropertyValue(object $object)
    {
        $value = parent::getPropertyValue($object);
        if ($value) {
            $value = base64_encode(serialize($value));
        }
        return $value;
    }

}

