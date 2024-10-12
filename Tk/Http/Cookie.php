<?php
namespace Tk\Http;

use Tk\Config;
use Tk\Date;

class Cookie
{

    /**
     * This is the cookie name that will be stored on the users browser, we can
     * identify that browser. It will bew refreshed on each call so the cookie
     * remains until the cache is cleared.
     * Usage:
     * ```
     *   $cookie->getBrowserId();
     * ```
     */
    const string BROWSER_ID = '___bid';

    /**
     *  default cookie expire
     */
    const int DAYS_30 = 30;

    const string SAMESITE_NONE   = 'None';
    const string SAMESITE_LAX    = 'Lax';
    const string SAMESITE_STRICT = 'Strict';

    protected string $path     = '/';
    protected string $domain   = '';
    protected bool   $secure   = true;
    protected bool   $httponly = true;
    protected string $samesite = self::SAMESITE_STRICT;


    public function __construct(
        ?string $domain  = null,
        ?string $path    = null,
        bool $secure     = true,
        bool $httponly   = true,
        string $samesite = self::SAMESITE_STRICT
    ) {
        $this->path = $path ?? Config::getBaseUrl();
        $this->domain = $domain ?? Config::getHostname();
        $this->secure = $secure;
        $this->httponly = $httponly;
        $this->samesite = $samesite;
        $this->getBrowserId();
    }

    public function all(): array
    {
        return $_COOKIE;
    }

    public function has(string $key): bool
    {
        return isset($_COOKIE[$key]);
    }

    public function get(string $key, ?string $default = ''): string
    {
        return $_COOKIE[$key] ?? $default;
    }

    protected function getCfg(?int $expires = null): array
    {
        return [
            'expires' => $expires ?? time() + Date::daysToSeconds(self::DAYS_30),
            'path' => $this->path,
            'domain' => $this->domain,
            'secure' => $this->secure,
            'httponly' => $this->httponly,
            'samesite' => $this->samesite,
        ];
    }

    public function set(string $key, string $value, ?int $expires = null): bool
    {
        if (headers_sent()) return false;
        $expires = $expires ?? time() + Date::daysToSeconds(self::DAYS_30);
        if (@setcookie($key, $value, $this->getCfg($expires))) {
            $_COOKIE[$key] = $value;
            return true;
        }
        return false;
    }

    public function delete(string $key): bool
    {
        if (headers_sent()) return false;
        if (setcookie($key, '', $this->getCfg(-1))) {
            unset($_COOKIE[$key]);
            unset($_REQUEST[$key]);
            return true;
        }
        return false;
    }

    public function getBrowserId(): string
    {
        $id = trim($this->get(self::BROWSER_ID, ''));
        if (!preg_match('/[0-9A-F]{32}/i', $id)) {
            $id = md5(
                time().
                ($_SERVER['REMOTE_ADDR'] ?? '1.1.1.1').
                ($_SERVER['HTTP_USER_AGENT'] ?? 'TK-UNKNOWN-AGENT')
            );
            //$id = md5(time().$this->getRequest()->getClientIp().$this->getRequest()->server->get('HTTP_USER_AGENT'));
        }
        $this->set(self::BROWSER_ID, $id);
        return $id;
    }

}