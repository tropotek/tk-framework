<?php
namespace Tk\DataMap\Form;

use Tk\DataMap\DataTypeInterface;

class Money extends DataTypeInterface
{

    protected string $currencyCode = 'AUD';


    public function setCurrencyCode(string $code): Money
    {
        $this->currencyCode = $code;
        return $this;
    }

    public function getPropertyValue(array $array): ?\Tk\Money
    {
        $value = parent::getPropertyValue($array);
        if ($this->isNullable() && is_null($value)) return null;
        if (!is_numeric($value)) {
            $value = 0.0;
        }
        $value = \Tk\Money::parseFromString(strval($value), \Tk\Currency::instance($this->currencyCode));
        return $value;
    }

    public function getColumnValue(object $object): string
    {
        $value = parent::getColumnValue($object);
        if ($value instanceof \Tk\Money) {
            return $value->toFloatString();
        }
        return strval($value);
    }

}

