<?php
namespace Tk;

/**
 * @author Tropotek <info@tropotek.com>
 * @created: 2/08/18
 * @link http://www.tropotek.com/
 * @license Copyright 2018 Tropotek
 */
class Money implements \Serializable
{

    /**
     * @var Currency
     */
    private $currency = null;

    /**
     * @var Currency
     */
    private $currencyCode = '';

    /**
     * The dollar amount in cents.
     * @var integer
     */
    protected $amount = 0;


    /**
     * @param integer $amount The amount in cents.
     * @param null|Currency $currency The currency, Default 'AUD'.
     */
    function __construct($amount = 0, $currency = null)
    {
        $this->amount = intval($amount);
        if (!$currency) {
            $currency = Currency::getInstance(Currency::$default);
        }
        $this->setCurrency($currency);
    }

    /**
     * @param int $amount
     * @param null|Currency $currency
     * @return static
     */
    static function create($amount = 0, $currency = null)
    {
        return new static($amount, $currency);
    }

    /**
     * Create a money object from a string representation
     *
     * @param string $amount An amount string: '200.00', '$200.00'
     * @param null|Currency $currency
     * @return Money Returns null on invalid format
     * @throws Exception
     */
    static function parseFromString($amount, $currency = null)
    {
        if (!$currency) {
            $currency = Currency::getInstance(Currency::$default);
        }
        $digits = $currency->getFractionDigits();
        //if (!preg_match("/^(\$)?(\-)?[0-9]+((\.)[0-9]{1,{$digits}})?$/", $amount)) {
        if (!preg_match("/(\$)?(\-)?[0-9]+((\.)[0-9]{1,{$digits}})?$/", $amount)) {
            return null;
        }
        $amount = str_replace(array(',', '$'), array('', ''), $amount);
        $amount = floatval($amount);
        return static::create($amount * 100, $currency);
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize(array('amount' => $this->amount, 'currencyCode' => $this->currencyCode));
    }

    /**
     * @param string $data
     * @throws Exception
     */
    public function unserialize($data)
    {
        $data = unserialize($data);
        vd($data, $this);
        $this->amount = $data['amount'];
        $this->setCurrency(Currency::getInstance($data['currencyCode']));
    }

    /**
     * @param Currency $currency
     * @return $this
     */
    public function setCurrency(Currency $currency)
    {
        $this->currency = $currency;
        $this->currencyCode = $currency->getCode();
        return $this;
    }

    /**
     * @return Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Returns the dollar amount in cents. 100 = $1
     *
     * @return integer
     */
    function getAmount()
    {
        return $this->amount;
    }


    /**
     * Adds the value of another instance of money and returns a new instance.
     *
     * @param Money $other
     * @return Money
     * @throws Exception
     */
    function add(Money $other)
    {
        $this->assertCurrency($other);
        return static::create($this->amount + $other->amount);
    }

    /**
     * Subtracts the value of another instance of money and returns a new instance.
     *
     * @param Money $other
     * @return Money
     * @throws Exception
     */
    function subtract(Money $other)
    {
        $this->assertCurrency($other);
        return static::create($this->amount - $other->amount);
    }

    /**
     * Divide the amount by the denominator.
     *
     * @param float $denominator
     * @return Money
     * @throws Exception
     */
    function divideBy($denominator)
    {
        if ($denominator === 0) {
            throw new Exception('Divide by zero exception.');
        }
        return static::create($this->amount / $denominator);
    }

    /**
     * Multiplies the value of the money by an amount and returns a new instance.
     *
     * @param double $multiplier
     * @return Money
     */
    function multiply($multiplyer)
    {
        return static::create((int)round($this->amount * $multiplyer), $this->currency);
    }

    /**
     * Test for the same currency instance
     *
     * @param Money $arg
     * @throws Exception
     */
    private function assertCurrency(Money $arg)
    {
        if ($this->currency !== $arg->currency) {
            throw new Exception('Money math currency instance mismatch.');
        }
    }


    /**
     * Return a string amount as a 2 point precision float. Eg: '200,000.00'
     *
     * @param string $decSep
     * @param string $thousandthSep
     * @return string
     */
    function toFloatString($decSep = '.', $thousandthSep = ',')
    {
        return number_format(($this->getAmount() / 100), $this->getCurrency()->getFractionDigits(), $decSep, $thousandthSep);
    }

    /**
     * Return a formatted string representing the currency EG: '$200,000.00'
     *
     * @param string $decSep
     * @param string $thousandthSep
     * @return string
     */
    function toString($decSep = '.', $thousandthSep = ',')
    {
        $strValue = $this->getCurrency()->getSymbol() . $this->toFloatString();
        return $strValue;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

}