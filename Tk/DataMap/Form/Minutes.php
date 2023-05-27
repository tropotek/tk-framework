<?php
namespace Tk\DataMap\Form;

use Tk\DataMap\DataTypeInterface;

/**
 * map a minute time type from a form to an object property
 */
class Minutes extends DataTypeInterface
{

    public function getKeyValue(array $array): mixed
    {
        $value = parent::getKeyValue($array);
        if ($value !== null && preg_match('/^([0-9]+):([0-9]+)$/', $value, $regs)) {
            $value = (int)($regs[1] * 60) + (int)$regs[2];
        }
        return $value;
    }

    public function getPropertyValue(object $object): mixed
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

