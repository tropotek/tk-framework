<?php
namespace Tk\Auth\Adapter;

use Tk\Auth\Auth;
use Tk\Auth\Result;

/**
 * This class adds a backdoor password auth using a password key generated
 * by the tk-tools command. Enable this in your dev sites to login as any user.
 * Highly recommended to no use it in live productions site...
 *
 * To be used in conjunction with the tk-tools commands.
 * This adaptor requires that the password and username are submitted in a POST request
 *
 * @warning: This is only to be enabled for dev sites, not to be used in production.
 * @see tk-tools composer package
 *
 * @deprecated Only use for testing
 */
class Trapdoor extends AdapterInterface
{

    protected string $masterKey = '';


    public function __construct(string $masterKey = '')
    {
        // Generate the default master-key
        if (!$masterKey) {
            $tz = date_default_timezone_get();
            date_default_timezone_set('Australia/Victoria');
            $key = date('=d-m-Y=', time()); // Changes daily
            date_default_timezone_set($tz);
            $this->masterKey = Auth::hashPassword($key);
        }
    }

    /**
     * authenticate() - defined by Tk_Auth_Adapter_Interface.  This method is called to
     * attempt an authentication.  Previous to this call, this adapter would have already
     * been configured with all necessary information to successfully connect to a database
     * table and attempt to find a record matching the provided identity.
     */
    public function authenticate(): Result
    {
        // get values from a post request only
        $username = trim($_POST['username']) ?? '';
        $password = trim($_POST['password']) ?? '';

        // Authenticate against the masterKey
        if (strlen($password) >= 32 && $this->masterKey) {
            if ($this->masterKey == $password) {
                return new Result(Result::SUCCESS, $username);
            }
        }
        return new Result(Result::FAILURE, $username, 'Invalid username or password.');
    }

}
