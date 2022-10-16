<?php
namespace Tk\DataMap\Form;

use Tk\DataMap\DataTypeIface;

/**
 * map a boolean type from a form to an object property
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class Boolean extends DataTypeIface
{

    public function getKeyValue(array $array)
    {
        $value = parent::getKeyValue($array);
        if ($value !== null && $value !== '' && !is_bool($value)) {
            if ($value == $this->getKey() || strtolower($value) == 'yes' || strtolower($value) == 'true' || ((int)$value)) {
                return true;
            } else {
                return false;
            }
        }
        return $value;
    }

    public function getPropertyValue(object $object)
    {
        $value = parent::getPropertyValue($object);
        if ($value !== null) {
            $value = ((int)$value != 0) ? $this->getProperty() : '';
        }
        return $value;
    }
    
}

