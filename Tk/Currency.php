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
 *
 * @author Tropotek <info@tropotek.com>
 * @created: 2/08/18
 * @link http://www.tropotek.com/
 * @license Copyright 2018 Tropotek
 */
class Currency
{

    /**
     * Default Currency code
     * @var string
     */
    public static $default = 'AUD';

    /**
     * @var array
     */
    public static $currencyList = array(
        'AUD' => array('name' => 'Australian Dollar', 'locale' => 'Australia', 'symbol' => '$', 'altSymbol' => 'AUD$', 'digits' => 2),
        'NZD' => array('name' => 'New Zealand Dollar', 'locale' => 'New Zealand', 'symbol' => '$', 'altSymbol' => 'NZD$', 'digits' => 2),
        'USD' => array('name' => 'US Dollar', 'locale' => 'United Stated Of America', 'symbol' => '$', 'altSymbol' => 'USD$', 'digits' => 2),
        'THB' => array('name' => 'Thai Baht', 'locale' => 'Thailand', 'symbol' => 'THB', 'altSymbol' => 'THB$', 'digits' => 2)
    );

    /**
     * @var array
     */
    private static $instance = array();

    /**
     * @var string
     */
    private $code = '';

    /**
     * @param string $currencyCode (optional)
     */
    private function __construct($currencyCode = 'AUD')
    {
        $this->code = $currencyCode;
    }

    /**
     * Returns the Currency instance for the given currency code.
     *
     * @param string $currencyCode
     * @return Currency
     */
    static function getInstance($currencyCode = 'AUD')
    {
        if (!array_key_exists($currencyCode, self::$currencyList)) {
            \Tk\Log::warning('Invalid Currency code: '.$currencyCode.', using default: ' . self::$default);
            $currencyCode = self::$default;
        }
        if (!isset(self::$instance[$currencyCode])) {
            self::$instance[$currencyCode] = new static($currencyCode);
        }
        return self::$instance[$currencyCode];
    }

    /**
     * @return string
     */
    function getName()
    {
        return self::$currencyList[$this->getCode()]['name'];
    }

    /**
     * Gets the ISO 4217 currency code of this currency.
     *
     * @return string
     */
    function getCode()
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
     *
     * @return string The symbol of this currency for the specified locale.
     */
    function getSymbol()
    {
        return self::$currencyList[$this->getCode()]['symbol'];
    }

    /**
     * Gets the default number of fraction digits used with this currency.
     *
     * For example, the default number of fraction digits for the Euro is 2,
     * while for the Japanese Yen it's 0. In the case of pseudo-currencies,
     * such as IMF Special Drawing Rights, -1 is returned.
     *
     * @return integer
     */
    function getFractionDigits()
    {
        return (int)self::$currencyList[$this->getCode()]['digits'];
    }

    /**
     * @return string
     */
    function getLocal()
    {
        return self::$currencyList[$this->getCode()]['locale'];
    }


}