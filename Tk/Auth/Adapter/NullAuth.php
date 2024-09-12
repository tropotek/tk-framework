<?php
namespace Tk\Auth\Adapter;

use Tk\Auth\Result;

/**
 * This object only checks for a valid username and returns a valid result
 * Use it for testing or if you require a username only login
 *
 * @deprecated Only use for testing
 */
class NullAuth extends AdapterInterface
{

    public function authenticate(): Result
    {
        // get values from a post or get request
        $username = $_REQUEST['username'];

        if (!$username) {
            return new Result(Result::FAILURE_CREDENTIAL_INVALID, $username, 'Invalid username or password.');
        }
        try {
            return new Result(Result::SUCCESS, $username);
        } catch (\Exception $e) {
            \Tk\Log::warning($e->getMessage());
        }
        return new Result(Result::FAILURE_CREDENTIAL_INVALID, '', 'Invalid credentials.');
    }

}
