<?php
namespace Tk\DataMap\Form;

use Tk\DataMap\DataTypeIface;

/**
 * map a minute time type from a form to an object property
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class Minutes extends DataTypeIface
{

    public function getKeyValue(array $array)
    {
        $value = parent::getKeyValue($array);
        if ($value !== null && preg_match('/^([0-9]+):([0-9]+)$/', $value, $regs)) {
            $value = (int)($regs[1] * 60) + (int)$regs[2];
        }
        return $value;
    }

    public function getPropertyValue(object $object)
    {
        $value = parent::getPropertyValue($object);
        if ($value !== null) {
            $h = $value/60;
            $m = $value%60;
            return sprintf('%d:%02d', $h, $m);
        }
        return '';
    }

}

