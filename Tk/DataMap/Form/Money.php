<?php
namespace Tk\DataMap\Form;

use Tk\DataMap\DataTypeInterface;

/**
 * map a integer type from a form to an object property
 */
class Money extends DataTypeInterface
{

    protected string $currencyCode = 'AUD';


    public function setCurrencyCode(string $code): Money
    {
        $this->currencyCode = $code;
        return $this;
    }

    public function getKeyValue(array $array): mixed
    {
        $value = parent::getKeyValue($array);
        if ($value !== null && !$value instanceof \Tk\Money) {
            $value = \Tk\Money::parseFromString($value, \Tk\Currency::getInstance($this->currencyCode));
        }
        return $value;
    }

    public function getPropertyValue(object $object): mixed
    {
        $value = parent::getPropertyValue($object);
        if ($value instanceof \Tk\Money) {
            return $value->toFloatString();
        }
        return $value;
    }

}

