<?php
namespace Tk\DataMap\Table;

use Tk\DataMap\DataTypeInterface;

/**
 * map a boolean type from a form to an object property
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class Boolean extends DataTypeInterface
{

    public function getKeyValue(array $array): mixed
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

    public function getPropertyValue(object $object): mixed
    {
        $value = parent::getPropertyValue($object);
        if ($value) return 'Yes';
        return 'No';
    }

}

