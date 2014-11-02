<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @author Darryl Ross <darryl.ross@aot.com.au>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk;

/**
 * The Tk\Money object.
 *
 * @package Tk
 */
class Money
{

    /**
     * @var int
     */
    private $amount = 0;

    /**
     * @var string
     */
    private $currencyCode = 'AUD';

    /**
     * @var Tk\Currency
     */
    private $currency = null;


    /**
     *
     * @param int $amount The amount in cents.
     * @param \Tk\Currency $currency The currency, Default 'AUD'.
     */
    public function __construct($amount = 0, Currency $currency = null)
    {
        $this->amount = intval($amount);
        if (!$currency) {
            $c = 'AUD';
            if (class_exists('\Tk\Config') && Config::getInstance()->exists('system.currency')) {
                $c = Config::getInstance()->get('system.currency');
            }
            $currency = Currency::getInstance($c);
        }
        $this->currency = $currency;
    }

    /**
     * Create a money object
     *
     * @param int $amount The amount in cents.
     * @param \Tk\Currency $currency The currency, Default 'AUD'.
     * @return \Tk\Money
     */
    static function create($amount = 0, Currency $currency = null)
    {
        return new self($amount, $currency);
    }

    /**
     * Create a money object from a string
     *
     * @param string $amount An amount string: '200.00', '$200.00'
     * @return \Tk\Money Returns null on invalid format
     */
    static function parseFromString($amount, Currency $currency = null)
    {
        $digits = $currency->getDefaultFractionDigits();
        if (!preg_match("/^(\$)?(\-)?[0-9]+((\.)[0-9]{1,{$digits}})?$/", $amount)) {
            return null;
        }
        $amount = str_replace(array(',', '$'), array('', ''), $amount);
        $amount = floatval($amount);
        return new self($amount * 100, $currency);
    }

    /**
     * Serialise Write.
     *
     * @return array
     */
    public function __sleep()
    {
        $this->currencyCode = $this->currency->getCurrencyCode();
        $class = "\0" . __CLASS__ . "\0";
        return array($class . "amount", $class . "currencyCode");
    }

    /**
     * Serialise Read
     *
     */
    public function __wakeup()
    {
        $this->currency = Currency::getInstance($this->currencyCode);
    }

    /**
     * Returns the amount in cents.
     *
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Returns the currency.
     *
     * @return \Tk\Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Adds the value of another instance of money and returns a new instance.
     *
     * @param \Tk\Money $other
     * @return \Tk\Money
     */
    public function add(Money $other)
    {
        $this->assertCurrency($other);
        return self::create($this->amount + $other->amount, $this->currency);
    }

    /**
     * Subtracts the value of another instance of money and returns a new instance.
     *
     * @param \Tk\Money $other
     * @return \Tk\Money
     */
    public function subtract(Money $other)
    {
        $this->assertCurrency($other);
        return self::create($this->amount - $other->amount, $this->currency);
    }

    /**
     * Divide the amount by the denominator.
     *
     * @param float $denominator
     */
    public function divideBy($denominator)
    {
        if ($denominator === 0) {
            throw new Exception('Divide by zero exception.');
        }
        return self::create($this->amount / $denominator, $this->currency);
    }

    /**
     * Multiplies the value of the money by an amount and returns a new instance.
     *
     * @param double $multiplyer
     * @return \Tk\Money
     */
    public function multiply($multiplyer)
    {
        return self::create((int)round($this->amount * $multiplyer), $this->currency);
    }

    /**
     * return an absolute value of this money object
     *
     * @return \Tk\Money
     */
    public function abs()
    {
        return self::create(abs($this->amount), $this->currency);
    }

    /**
     * Compares the value to another instance of money.
     *
     * @param \Tk\Money $other
     * @return int Returns the difference, 0 = equal.
     */
    public function compareTo(Money $other)
    {
        $this->assertCurrency($other);
        return $this->getAmount() - $other->getAmount();
    }

    /**
     * Checks if the money value is greater than the value of another instance of money.
     *
     * @param \Tk\Money $other
     * @return bool
     */
    public function greaterThan(Money $other)
    {
        return $this->compareTo($other) > 0;
    }

    /**
     * Checks if the money value is greater than or equal the value of another instance of money.
     *
     * @param \Tk\Money $other
     * @return bool
     */
    public function greaterThanEqual(Money $other)
    {
        return $this->compareTo($other) >= 0;
    }

    /**
     * Checks if the money value is less than the value of another instance of money.
     *
     * @param \Tk\Money $other
     * @return bool
     */
    public function lessThan(Money $other)
    {
        return $this->compareTo($other) < 0;
    }

    /**
     * Checks if the money value is less than or equal the value of another instance of money.
     *
     * @param \Tk\Money $other
     * @return bool
     */
    public function lessThanEqual(Money $other)
    {
        return $this->compareTo($other) <= 0;
    }

    /**
     * Checks if the money value is equal to the value of another instance of money.
     *
     * @param \Tk\Money $other
     * @return bool
     */
    public function equals(Money $other)
    {
        return ($this->compareTo($other) == 0);
    }

    /**
     * Return a formatted string to the nearest dollar representing the currency
     *
     * @return string
     */
    public function toNearestDollarString()
    {
        $amount = round(($this->getAmount() / 100) + .205);
        return $this->currency->getSymbol() . $amount;
    }

    /**
     * Return a string amount as a 2 point presision float. Eg: '200.00'
     *
     * @return string
     */
    public function toFloatString()
    {
        return sprintf("%.02f", ($this->getAmount() / 100));
    }

    /**
     * Return a formatted string representing the currency
     *
     * @return string
     */
    public function toString($decSep = '.', $thousandthSep = ',')
    {
        $strValue = $this->currency->getSymbol($this->currency->getCurrencyCode()) . number_format(($this->getAmount() / 100),
                $this->currency->getDefaultFractionDigits(), $decSep, $thousandthSep);
        return $strValue;
    }

    /**
     * Test for the same currency instance
     *
     * @param \Tk\Money $arg
     */
    private function assertCurrency(Money $arg)
    {
        if ($this->currency !== $arg->currency) {
            throw new Exception('Currency instance mismatch.');
        }
    }

}
