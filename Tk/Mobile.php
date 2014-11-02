<?php
/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk;

/**
 * Tk\Mobile Detect
 *
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @package Tk
 */
class Mobile
{

    /**
     * @var \Tk\Mobile
     */
    static $instance = null;

	protected $accept;
	protected $userAgent;
	protected $isMobile = false;
	protected $isAndroid = null;
	protected $isAndroidtablet = null;
	protected $isIphone = null;
	protected $isIpad = null;
	protected $isBlackberry = null;
	protected $isBlackberrytablet = null;
	protected $isOpera = null;
	protected $isPalm = null;
	protected $isWindows = null;
	protected $isWindowsphone = null;
	protected $isSymbian = null;
	protected $isGeneric = null;
	protected $devices = array(
		"android" => "android.*mobile",
		"androidtablet" => "android(?!.*mobile)",
		"blackberry" => "blackberry",
		"blackberrytablet" => "rim tablet os",
		"iphone" => "(iphone|ipod)",
		"ipad" => "(ipad)",
		"palm" => "(avantgo|blazer|elaine|hiptop|palm|plucker|xiino)",
		"windows" => "windows ce; (iemobile|ppc|smartphone)",
		"windowsphone" => "windows phone os",
		"symbian" => "(symbian)",
		"generic" => "(kindle|mobile|mmp|midp|pocket|psp|smartphone|treo|up.browser|up.link|vodafone|wap|opera mini)"
	);

	public function __construct()
	{
		$this->userAgent = $_SERVER['HTTP_USER_AGENT'];
		$this->accept = $_SERVER['HTTP_ACCEPT'];

		if (isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE'])) {
			$this->isMobile = true;
		} elseif (strpos($this->accept, 'text/vnd.wap.wml') > 0 || strpos($this->accept, 'application/vnd.wap.xhtml+xml') > 0) {
			$this->isMobile = true;
		} else {
			foreach ($this->devices as $device => $regexp) {
				if ($this->isDevice($device)) {
					$this->isMobile = true;
				}
			}
		}
	}

    /**
     * get instance once per session is all thats needed
     *
     * @return \Tk\Mobile
     */
    static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }


	/**
	 * Overloads isSymbian | isAndroid() | isAndroidtablet() | isIphone() | isIpad() | isBlackberry() |
     *   isBlackberrytablet() | isPalm() | isWindowsphone() | isWindows() | isGeneric()
	 *
	 * @param string $name
	 * @param array $arguments
	 * @return bool
	 */
	public function __call($name, $arguments)
	{
		$device = substr($name, 2);
		if ($name == "is" . ucfirst($device) && array_key_exists(strtolower($device), $this->devices)) {
			return $this->isDevice($device);
		} else {
            throw new Exception("Method $name not defined", \E_USER_WARNING);
            //trigger_error("Method $name not defined", \E_USER_WARNING);
		}
	}

	/**
	 * Returns true if any type of mobile device detected, including special ones
	 * @return bool
	 */
	public function isMobile()
	{
		return $this->isMobile;
	}

    /**
     * isDevice
     *
     * @param string $device
     * @return bool
     */
	protected function isDevice($device)
	{
		$var = "is" . ucfirst($device);
		$return = $this->$var === null ? (bool)preg_match("/" . $this->devices[strtolower($device)] . "/i",
            $this->userAgent) : $this->$var;
		if ($device != 'generic' && $return == true) {
			$this->isGeneric = false;
		}

		return $return;
	}

}
