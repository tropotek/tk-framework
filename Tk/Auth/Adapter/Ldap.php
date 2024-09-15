<?php
namespace Tk\Auth\Adapter;

use LDAP\Connection;
use Tk\Auth\Result;

/**
 * LDAP Authentication adapter
 *
 * This adapter requires that the password and username are submitted in a POST request
 */
class Ldap extends AdapterInterface
{

    protected string $host      = '';
    protected int $port         = 636;
    protected bool $tls         = false;
    protected string $baseDn    = '';
    protected ?Connection $ldap = null;


    public function __construct(string $host, string $baseDn, int $port = 636, bool $tls = false)
    {
        $this->setHost($host);
        $this->setBaseDn($baseDn);
        if ($port <= 0) $port = 636;
        $this->setPort($port);
        $this->setTls($tls);
    }

    /**
     * Authenticate the user
     *
     * @return Result
     */
    public function authenticate(): Result
    {
        // get values from a post request only
        $username = trim($_POST['username']) ?? '';
        $password = trim($_POST['password']) ?? '';

        if (!$username || !$password) {
            return new Result(Result::FAILURE_CREDENTIAL_INVALID, $username, '0000 Invalid username or password.');
        }
        try {
            $this->ldap = @ldap_connect($this->getHost(), $this->getPort());
            if ($this->isTls())
                @ldap_start_tls($this->getLdap());

            $this->setBaseDn(sprintf($this->getBaseDn(), $username));
            // legacy check (remove in future versions)
            $this->setBaseDn(str_replace('{username}', $username, $this->getBaseDn()));

            if (@ldap_bind($this->getLdap(), $this->getBaseDn(), $password)) {
                return new Result(Result::SUCCESS, $username);
            }
        } catch (\Exception $e) {
            \Tk\Log::notice($e->getMessage());
        }

        return new Result(Result::FAILURE_CREDENTIAL_INVALID, $username, '0001 Invalid username or password.');
    }

    /**
     * @param array|string $filter
     * @return array|false|null
     */
    public function ldapSearch($filter)
    {
        $ldapData = null;
        if ($this->ldap) {
            $sr = @ldap_search($this->getLdap(), $this->getBaseDn(), $filter);
            $ldapData = @ldap_get_entries($this->getLdap(), $sr);
        }
        return $ldapData;
    }


    /**
     * @return null|resource
     */
    public function getLdap()
    {
        return $this->ldap;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): Ldap
    {
        $this->host = $host;
        return $this;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function setPort(int $port): Ldap
    {
        $this->port = $port;
        return $this;
    }

    public function isTls(): bool
    {
        return $this->tls;
    }

    public function setTls(bool $tls): Ldap
    {
        $this->tls = $tls;
        return $this;
    }

    public function getBaseDn(): string
    {
        return $this->baseDn;
    }

    public function setBaseDn(string $baseDn): Ldap
    {
        $this->baseDn = $baseDn;
        return $this;
    }

}
