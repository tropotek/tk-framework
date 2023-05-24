<?php
namespace Tk\DataMap\Form;

use Tk\DataMap\DataTypeInterface;

/**
 * map a File upload type from a form to an object property
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class File extends DataTypeInterface
{

    public function getKeyValue(array $array): mixed
    {
        $value = parent::getKeyValue($array);
        $value .= '';
        vd($array);
        return $value;
    }

    public function getPropertyValue(object $object): mixed
    {
        $value = parent::getPropertyValue($object);
        $value .= '';
        vd($value);
        return $value;
    }

}

