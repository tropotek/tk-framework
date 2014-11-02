<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Auth\Adapter;

/**
 * Digest Authentication adapter
 *
 * Config options:
 *
 * $config['system.auth.loginAdapters'] = array( 'Digest File' => '\Tk\Auth\Adapter\Digest' );
 * $config['system.auth.digest.realm'] = 'realmName';
 * $config[''system.auth.digest.filename'] = '/path/to/digest/.htpassword';
 *
 *
 */
class Digest extends Iface
{


    /**
     * Constructor
     *
     * @param  string $username The username of the account being authenticated
     * @param  string $password The password of the account being authenticated
     * @param  array  $options  An array of config options, if null then $config[`system.auth.ldap`] is used
     */
    public function __construct($username = null, $password = null, $options = null)
    {
        if (!is_array($options) || !count($this->getConfig()->getGroup('system.auth.digest'))) {
            $options = $this->getConfig()->getGroup('system.auth.digest');
        }
        parent::__construct($username, $password, $options);
    }


    /**
     * Returns the filename option value or null if it has not yet been set
     *
     * @return string|null
     */
    public function getFilename()
    {
        return $this->getOption('system.auth.digest.filename');
    }

    /**
     * Returns the realm option value or null if it has not yet been set
     *
     * @return string|null
     */
    public function getRealm()
    {
        return $this->getOption('system.auth.digest.realm');
    }


    /**
     * Defined by Tk\Auth\Adapter\Iface
     *
     * @throws \Tk\Auth\Exception
     * @return \Tk\Auth\Result
     */
    public function authenticate()
    {
        if (false === ($fileHandle = @fopen($this->getFilename(), 'r'))) {
            tklog("Cannot open '{$this->getFilename()}' for reading");
            return $this->makeResult($result['code'] = \Tk\Auth\Result::FAILURE_IDENTITY_NOT_FOUND, "Cannot open digest file.");
        }
        $id       = $this->getUsername() . ':' . $this->getRealm();
        $idLength = strlen($id);
        while ($line = trim(fgets($fileHandle))) {
            if (substr($line, 0, $idLength) === $id) {
                if ( $this->_secureStringCompare(substr($line, -32), md5(sprintf('%s:%s:%s', $this->getRealm(), $this->getPassword()))) ) {
                    return $this->makeResult(\Tk\Auth\Result::SUCCESS);
                } else {
                    return $this->makeResult(\Tk\Auth\Result::FAILURE_CREDENTIAL_INVALID, 'Username or Password incorrect');
                }
            }
        }
        return $this->makeResult($result['code'] = \Tk\Auth\Result::FAILURE_IDENTITY_NOT_FOUND, "Invalid user credentials.");
    }

    /**
     * Securely compare two strings for equality while avoided C level memcmp()
     * optimisations capable of leaking timing information useful to an attacker
     * attempting to iteratively guess the unknown string (e.g. password) being
     * compared against.
     *
     * @param string $a
     * @param string $b
     * @return bool
     */
    protected function _secureStringCompare($a, $b)
    {
        if (strlen($a) !== strlen($b)) {
            return false;
        }
        $result = 0;
        for ($i = 0; $i < strlen($a); $i++) {
            $result |= ord($a[$i]) ^ ord($b[$i]);
        }
        return $result == 0;
    }
}

