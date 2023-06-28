<?php
namespace Tk;

use Tk\Traits\SystemTrait;

class Cookie
{
    use SystemTrait;

    /**
     * This is the cookie name that will be stored on the users browser, we can
     * identify that browser. It will bew refreshed on each call so the cookie
     * remains until the cache is cleared.
     * Usage:
     * ```
     *   $cookie->getBrowserId();
     * ```
     */
    const BROWSER_ID = '___bid';

    /**
     *  30 days in seconds
     */
    const DAYS_30_SEC = 60*60*24*30;

    const SAMESITE_NONE   = 'None';
    const SAMESITE_LAX    = 'Lax';
    const SAMESITE_STRICT = 'Strict';

    protected string $path = '/';

    protected string $domain = '';

    protected bool $secure = true;

    protected bool $httponly = true;

    protected string $samesite = self::SAMESITE_STRICT;


    public function __construct(
        ?string $domain  = null,
        ?string $path    = null,
        bool $secure     = true,
        bool $httponly   = true,
        string $samesite = self::SAMESITE_STRICT
    ) {
        $this->path = $path ?? $this->getConfig()->getBaseUrl();
        $this->domain = $domain ?? $this->getConfig()->getHostname();
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

    public function set(string $key, string $value, ?int $expires = null): bool
    {
        if (headers_sent()) return false;
        $cfg = [
            'expires' => $expires ?? time() + self::DAYS_30_SEC,
            'path' => $this->path,
            'domain' => $this->domain,
            'secure' => $this->secure,
            'httponly' => $this->httponly,
            'samesite' => $this->samesite,
        ];
        if (@setcookie($key, $value, $cfg)) {
            $_COOKIE[$key] = $value;
            return true;
        }
        return false;
    }

    public function delete(string $key): bool
    {
        if (headers_sent()) return false;
        $cfg = [
            'expires' => -1,
            'path' => $this->path,
            'domain' => $this->domain,
            'secure' => $this->secure,
            'httponly' => $this->httponly,
            'samesite' => $this->samesite,
        ];
        if (setcookie($key, '', $cfg)) {
            unset($_COOKIE[$key]);
            unset($_REQUEST[$key]);
            return true;
        }
        return false;
    }

    public function getBrowserId(): string
    {
        $id = trim($this->get(self::BROWSER_ID)) ?? '';
        if (!preg_match('/[0-9A-F]{32}/i', $id)) {
            $id = md5(time().$this->getRequest()->getClientIp().$this->getRequest()->server->get('HTTP_USER_AGENT'));
        }
        $this->set(self::BROWSER_ID, $id);
        return $id;
    }

}