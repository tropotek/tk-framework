<?php
namespace Tk\DataMap\Db;

use Tk\DataMap\DataTypeIface;

/**
 * map a Money type from a DB field to an object property
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class Money extends DataTypeIface
{

    protected string $currencyCode = 'AUD';


    public function setCurrencyCode(string $code): Money
    {
        $this->currencyCode = $code;
        return $this;
    }

    public function getKeyValue(array $array)
    {
        $value = parent::getKeyValue($array);
        if ($value !== null) {
            $value = \Tk\Money::create($value, \Tk\Currency::getInstance($this->currencyCode));
        }
        return $value;
    }

    public function getPropertyValue(object $object)
    {
        $value = parent::getPropertyValue($object);
        if ($value !== null && $value instanceof \Tk\Money) {
            return $value->getAmount();
        }
        return $value;
    }
    
}

