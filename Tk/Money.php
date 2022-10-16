<?php
namespace Tk;

/**
 * @author Tropotek <http://www.tropotek.com/>
 */
class Money implements \Serializable
{

    private Currency $currency;

    private string $currencyCode = '';

    /**
     * The dollar amount in cents.
     */
    protected int $amount = 0;


    /**
     * @param integer $amount The amount in cents.
     * @param null|Currency $currency The currency, Default 'AUD'.
     */
    public function __construct(int $amount = 0, ?Currency $currency = null)
    {
        $this->amount = $amount;
        if (!$currency) {
            $currency = Currency::getInstance(Currency::$DEFAULT);
        }
        $this->setCurrency($currency);
    }

    /**
     * @param integer $amount The amount in cents.
     * @param null|Currency $currency The currency, Default 'AUD'.
     */
    public static function create(int $amount = 0, ?Currency $currency = null): Money
    {
        if ($amount instanceof Money) return $amount;
        return new static($amount, $currency);
    }

    /**
     * Create a money object from a string representation
     *
     * @param string $amount An amount string: '20,000.00', '$20,000.00'
     * @param null|Currency $currency
     * @param string $thousandthSep Default ','
     * @return Money Returns null on invalid format
     */
    public static function parseFromString(string $amount, ?Currency $currency = null, string $thousandthSep = ','): Money
    {
        if (!$currency) {
            $currency = Currency::getInstance(Currency::$DEFAULT);
        }
        $digits = $currency->getFractionDigits();
        //if (!preg_match("/^(\$)?(\-)?[0-9]+((\.)[0-9]{1,{$digits}})?$/", $amount)) {
        $amt = str_replace(array($thousandthSep, $currency->getSymbol(), $currency->getLocal()), '', $amount);
        if (!preg_match("/(\-)?[0-9]+((\.)[0-9]{1,{$digits}})?$/", $amt)) {
            Log::warning('Cannot parse amount string: ' . $amount);
            return static::create();
        }
        $amt = floatval($amt);
        return static::create($amt * 100, $currency);
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize(['amount' => $this->amount, 'currencyCode' => $this->currencyCode]);
    }

    /**
     * @param string $data
     */
    public function unserialize($data)
    {
        $data = unserialize($data);
        $this->amount = $data['amount'];
        $this->setCurrency(Currency::getInstance($data['currencyCode']));
    }

    protected function setCurrency(Currency $currency): Money
    {
        $this->currency = $currency;
        $this->currencyCode = $currency->getCode();
        return $this;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    /**
     * Returns the dollar amount in cents. 100 = $1
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * Adds the value of another instance of money and returns a new instance.
     */
    public function add(Money $other): Money
    {
        $this->assertCurrency($other);
        return static::create($this->getAmount() + $other->getAmount());
    }

    /**
     * Subtracts the value of another instance of money and returns a new instance.
     */
    public function subtract(Money $other): Money
    {
        $this->assertCurrency($other);
        return static::create($this->getAmount() - $other->getAmount());
    }

    /**
     * Divide the amount by the denominator.
     * @throws Exception
     */
    public function divideBy(float $denominator): Money
    {
        if ($denominator == 0) {
            throw new Exception('Divide by zero exception.');
        }
        return static::create($this->getAmount() / $denominator);
    }

    /**
     * Multiplies the value of the money by an amount and returns a new instance.
     */
    public function multiply(int $multiplier): Money
    {
        return static::create((int)round($this->getAmount() * $multiplier), $this->getCurrency());
    }

    /**
     * Compares the value to another instance of money.
     *
     * @return integer Returns the difference, 0 = equal.
     */
    public function compareTo(Money $other): int
    {
        $this->assertCurrency($other);
        return $this->getAmount() - $other->getAmount();
    }
    
    /**
     * Checks if the money value is greater than the value of another instance of money.
     */
    public function greaterThan(Money $other): bool
    {
        return $this->compareTo($other) > 0;
    }
    
    /**
     * Checks if the money value is greater than or equal the value of another instance of money.
     */
    public function greaterThanEqual(Money $other): bool
    {
        return ($this->compareTo($other) > 0) || ($other->getAmount() == $this->getAmount());
    }
    
    /**
     * Checks if the money value is less than the value of another instance of money.
     */
    public function lessThan(Money $other): bool
    {
        return $this->compareTo($other) < 0;
    }
    
    /**
     * Checks if the money value is less than or equal the value of another instance of money.
     */
    public function lessThanEqual(Money $other): bool
    {
        return ($this->compareTo($other) < 0) || ($other->getAmount() == $this->getAmount());
    }
    
    /**
     * Checks if the money value is equal to the value of another instance of money.
     */
    public function equals(Money $other): bool
    {
        return ($this->compareTo($other) == 0);
    }

    /**
     * Test for the same currency instance
     */
    private function assertCurrency(Money $arg): bool
    {
        if ($this->getCurrency() !== $arg->getCurrency()) {
            Log::error('Money currency instance mismatch ['.$arg->getCurrency()->getCode().' => '.$this->getCurrency()->getCode().'].');
            return false;
        }
        return true;
    }

    /**
     * Return a string amount as a 2 point precision float. Eg: '2000.00'
     */
    public function toFloatString(string $decSep = '.', string $thousandthSep = ''): string
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
    public function toString(string $decSep = '.', string $thousandthSep = ''): string
    {
        return $this->getCurrency()->getSymbol() . $this->toFloatString($decSep, $thousandthSep);
    }

    public function __toString(): string
    {
        return $this->toString();
    }

}