<?php
namespace Tk;

class Money
{

    private Currency $currency;

    private string $currencyCode = '';

    /**
     * The dollar amount in cents.
     */
    protected int $amount = 0;


    public function __construct(int $amount = 0, ?Currency $currency = null)
    {
        $this->amount = $amount;
        if (!$currency) {
            $currency = Currency::getInstance(Currency::$DEFAULT);
        }
        $this->setCurrency($currency);
    }

    public static function create(int|Money $amount = 0, ?Currency $currency = null): Money
    {
        if ($amount instanceof Money) return $amount;
        return new static($amount, $currency);
    }

    /**
     * Create a money object from a string representation
     */
    public static function parseFromString(string $amount, ?Currency $currency = null, string $thousandthSep = ','): Money
    {
        if (!$currency) {
            $currency = Currency::getInstance(Currency::$DEFAULT);
        }
        $digits = $currency->getFractionDigits();
        //if (!preg_match("/^(\$)?(\-)?[0-9]+((\.)[0-9]{1,{$digits}})?$/", $amount)) {
        $amt = str_replace([$thousandthSep, $currency->getSymbol(), $currency->getLocal()], '', $amount);
        if (!preg_match("/(\-)?[0-9]+((\.)[0-9]{1,{$digits}})?$/", $amt)) {
            Log::notice('Cannot parse amount string: ' . $amount);
            return static::create();
        }
        $amt = floatval($amt);
        return static::create($amt * 100, $currency);
    }

    public function __serialize(): array
    {
        return ['amount' => $this->amount, 'currencyCode' => $this->currencyCode];
    }

    public function __unserialize(array $data)
    {
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
    private function assertCurrency(Money $arg): void
    {
        if ($this->getCurrency() !== $arg->getCurrency()) {
            throw new Exception('Money currency instance mismatch ['.$arg->getCurrency()->getCode().' => '.$this->getCurrency()->getCode().'].');
        }
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