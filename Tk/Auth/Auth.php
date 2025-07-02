<?php
namespace Tk\Auth;

use Tk\Auth\Adapter\AdapterInterface;
use Tk\Auth\Storage\StorageInterface;

/**
 * This Auth object validates a user and manages a user session/cookie/object
 */
class Auth
{
    public ?Result $loginResult = null;
    protected StorageInterface $storage;
    protected AdapterInterface $adapter;


    public function __construct(StorageInterface $storage, ?AdapterInterface $adapter = null)
    {
        $this->storage = $storage;
        $this->setAdapter($adapter);
    }

    /**
     * Returns true if an identity value s available from storage
     */
    public function hasIdentity(): bool
    {
        return !$this->getStorage()->isEmpty();
    }

    /**
     * Returns the user identity value from storage or null if non is available
     */
    public function getIdentity(): mixed
    {
        $storage = $this->getStorage();
        if (!$storage->isEmpty()) {
            return $storage->read();
        }
        return null;
    }

    /**
     * Returns the persistent storage handler
     */
    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    public function setAdapter(?AdapterInterface $adapter): Auth
    {
        if (is_null($adapter)) return $this;
        $this->adapter = $adapter;
        return $this;
    }

    public function getAdapter(): ?AdapterInterface
    {
        return $this->adapter;
    }

    /**
     * Authenticates against the supplied adapter
     */
    public function authenticate(string $username = '', string $password = ''): Result
    {
        if (is_null($this->getAdapter())) {
            throw new \Tk\Exception('No authentication adapter set');
        }

        if ($this->hasIdentity()) {
            $this->clearIdentity();
        }

        $loginResult = $this->getAdapter()?->authenticate($username, $password);
        if ($loginResult->isValid()) {
            $this->getStorage()->write($loginResult->getIdentity());
        }

        return $loginResult;
    }

    /**
     * Clears the user details from persistent storage
     */
    public function clearIdentity(): Auth
    {
        $this->getStorage()->clear();
        return $this;
    }


    /**
     * Use this to hash a password string.
     * The salt is usually a unique user hash.
     * Store this in the user table and compare against on login
     */
    public static function hashPassword(string $pwd, ?string $salt = null, string $algo = 'md5'): string
    {
        $str = $pwd;
        if ($salt) $str .= $salt;
        return hash($algo, $str);
    }

    /**
     * Generate a random password
     */
    public static function createPassword(int $length = 8): string
    {
        $chars = '234567890abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ';
        $i = 0;
        $password = '';
        while ($i <= $length) {
            $password .= $chars[mt_rand(0, strlen($chars) - 1)];
            $i++;
        }
        return $password;
    }

}
