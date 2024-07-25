<?php
namespace Tt\DataMap\Db;

use Tt\DataMap\DataTypeInterface;

/**
 * map a Money type from a DB field to an object property
 * The returned column values is an int cents 100 = $1
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
            $value = \Tk\Money::create((int)$value, \Tk\Currency::getInstance($this->currencyCode));
        }
        return $value;
    }

    public function getColumnValue(object $object): mixed
    {
        $value = parent::getColumnValue($object);
        if ($value instanceof \Tk\Money) {
            return $value->getAmount();
        }
        return $value;
    }

}