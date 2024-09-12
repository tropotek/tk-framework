<?php
namespace Tk\Auth;

use Tk\Auth\Storage\StorageInterface;

/**
 * This Auth object validates a user and manages a user session/cookie/object
 */
class Auth
{
    public ?Result $loginResult = null;
    protected StorageInterface $storage;


    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
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

    /**
     * Authenticates against the supplied adapter
     */
    public function authenticate(Adapter\AdapterInterface $adapter): Result
    {
        if ($this->hasIdentity()) {
            $this->clearIdentity();
        }
        $loginResult = $adapter->authenticate();
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
    public static function hashPassword(string $pwd, string $salt = null, string $algo = 'md5'): string
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
