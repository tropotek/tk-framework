<?php
namespace Tk\Auth\Adapter;

use Tk\Auth\Result;

/**
 * This object only checks for a valid username and returns a valid result
 * Use it for testing or if you require a username only login
 *
 * @deprecated Not for production sites
 */
class NullAuth extends AdapterInterface
{

    public function authenticate(string $username = '', string $password = ''): Result
    {
        if (!$username) {
            return new Result(Result::FAILURE_CREDENTIAL_INVALID, $username, 'Invalid username or password.');
        }
        try {
            return new Result(Result::SUCCESS, $username);
        } catch (\Exception $e) {
            \Tk\Log::notice($e->getMessage());
        }
        return new Result(Result::FAILURE_CREDENTIAL_INVALID, '', 'Invalid credentials.');
    }

}
