<?php
namespace Tk\Auth\Adapter;

use Bs\Auth;
use Tk\Auth\Result;
use Tk\Db;

/**
 * A DB table authenticator adaptor
 *
 * This adaptor requires that the password and username are submitted in a POST request
 */
class DbTable extends AdapterInterface
{

    protected string $tableName      = 'user';
    protected string $usernameColumn = 'username';
    protected string $passwordColumn = 'password';


    public function __construct(string $tableName = 'user', string $userColumn = 'username', string $passColumn = 'password')
    {
        $this->tableName      = Db::escapeTable($tableName);
        $this->usernameColumn = Db::escapeTable($userColumn);
        $this->passwordColumn = Db::escapeTable($passColumn);
    }

    protected function getUserRow(string $username): ?object
    {
        if (!trim($username)) return null;
        $sql = sprintf("SELECT * FROM %s WHERE active AND %s = :username LIMIT 1",
            $this->tableName,
            $this->usernameColumn
        );
        return Db::queryOne($sql, compact('username'));
    }

    public function authenticate(): Result
    {
        // get values from a post request only
        $username = trim($_POST['username']) ?? '';
        $password = trim($_POST['password']) ?? '';

        if (!$username || !$password) {
            return new Result(Result::FAILURE_CREDENTIAL_INVALID, $username, 'No username or password.');
        }

        try {
            $user = $this->getUserRow($username);
            if ($user && password_verify($password, $user->{$this->passwordColumn})) {
                return new Result(Result::SUCCESS, $username);
            }
        } catch (\Exception $e) {
            \Tk\Log::notice($e->__toString());
        }
        return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, $username, 'Invalid username or password.');
    }

}
