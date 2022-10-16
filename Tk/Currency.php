<?php
namespace Tk;

/**
 * Represents a currency.
 * Currencies are identified by their currency codes.
 *  o AUSTRALIA    Australian Dollar   AUD  036
 *  o NEW ZEALAND  New Zealand Dollar  NZD  554
 *
 * The class is designed so that there's never more than one Currency instance
 * for any given currency. Therefore, there's no public constructor. You obtain
 * a Currency instance using the getInstance methods.
 *
 * @link http://www.iso.org/iso/en/prods-services/popstds/currencycodeslist.html
 * @author Tropotek <http://www.tropotek.com/>
 */
class Currency
{
    private static array $_INSTANCE = [];

    public static string $DEFAULT = 'AUD';

    public static array $CURRENCY_LIST = [
        'AUD' => ['name' => 'Australian Dollar', 'locale' => 'Australia', 'symbol' => '$', 'altSymbol' => 'AUD$', 'digits' => 2],
        'NZD' => ['name' => 'New Zealand Dollar', 'locale' => 'New Zealand', 'symbol' => '$', 'altSymbol' => 'NZD$', 'digits' => 2],
        'USD' => ['name' => 'US Dollar', 'locale' => 'United Stated Of America', 'symbol' => '$', 'altSymbol' => 'USD$', 'digits' => 2],
        'THB' => ['name' => 'Thai Baht', 'locale' => 'Thailand', 'symbol' => 'THB', 'altSymbol' => 'THB$', 'digits' => 2],
    ];

    private string $code;


    private function __construct(string $currencyCode = 'AUD')
    {
        $this->code = $currencyCode;
    }

    /**
     * Returns the Currency instance for the given currency code.
     */
    public static function getInstance(string $currencyCode = 'AUD'): Currency
    {
        if (!array_key_exists($currencyCode, self::$CURRENCY_LIST)) {
            Log::warning('Invalid Currency code: '.$currencyCode.', using default: ' . self::$DEFAULT);
            $currencyCode = self::$DEFAULT;
        }
        if (!isset(self::$_INSTANCE[$currencyCode])) {
            self::$_INSTANCE[$currencyCode] = new static($currencyCode);
        }
        return self::$_INSTANCE[$currencyCode];
    }

    function getName(): string
    {
        return self::$CURRENCY_LIST[$this->getCode()]['name'];
    }

    /**
     * Gets the ISO 4217 currency code of this currency.
     */
    function getCode(): string
    {
        return $this->code;
    }

    /**
     * Gets the symbol of this currency for the specified locale.
     *
     * For example, for the US Dollar, the symbol is "$" if the specified
     * locale is the US, while for other locales it may be "US$". If no
     * symbol can be determined, the ISO 4217 currency code is returned.
     *
     * If locale is null, then the default locale is used.
     */
    function getSymbol(): string
    {
        return self::$CURRENCY_LIST[$this->getCode()]['symbol'];
    }

    /**
     * Gets the default number of fraction digits used with this currency.
     *
     * For example, the default number of fraction digits for the Euro is 2,
     * while for the Japanese Yen it's 0. In the case of pseudo-currencies,
     * such as IMF Special Drawing Rights, -1 is returned.
     */
    function getFractionDigits(): int
    {
        return (int)self::$CURRENCY_LIST[$this->getCode()]['digits'];
    }

    function getLocal(): string
    {
        return self::$CURRENCY_LIST[$this->getCode()]['locale'];
    }

}