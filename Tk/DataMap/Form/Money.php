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
        if (is_numeric($value)) {
            $value = \Tk\Money::parseFromString($value, \Tk\Currency::instance($this->currencyCode));
        }
        if (empty($value)) $value = null;
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

