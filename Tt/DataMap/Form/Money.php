<?php
namespace Tt\DataMap\Form;

use Tt\DataMap\DataTypeInterface;

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

    public function getPropertyValue(array $array): mixed
    {
        $value = parent::getPropertyValue($array);
        if (is_numeric($value)) {
            $value = \Tk\Money::parseFromString($value, \Tk\Currency::getInstance($this->currencyCode));
        }
        return $value;
    }

    public function getColumnValue(object $object): mixed
    {
        $value = parent::getColumnValue($object);
        if ($value instanceof \Tk\Money) {
            return $value->toFloatString();
        }
        return $value;
    }

}

