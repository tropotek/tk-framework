<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Auth\Adapter;

/**
 * A DB table authenticator adaptor
 *
 * Config options:
 *
 * $config['system.auth.loginAdapters'] = array( 'Digest File' => '\Tk\Auth\Adapter\DbTable' );
 * $config['system.auth.dbTable.tableName'] = 'dbTable';
 * $config['system.auth.dbTable.usernameColumn'] = 'username';
 * $config['system.auth.dbTable.passwordColumn'] = 'password';
 * $config['system.auth.dbTable.sqlWhere'] = '';
 *
 *
 *
 */
class DbTable extends  Iface
{


    /**
     * Constructor
     *
     * @param  string $username The username of the account being authenticated
     * @param  string $password The password of the account being authenticated
     * @param  array $options An array of config options, if null then $config[`system.auth.ldap`] is used
     * @throws \Tk\Auth\Exception
     */
    public function __construct($username = null, $password = null, $options = null)
    {
        if (!is_array($options) || !count($this->getConfig()->getGroup('system.auth.dbTable'))) {
            $options = $this->getConfig()->getGroup('system.auth.dbTable');
        }
        if (!$options['system.auth.dbTable.tableName']) {
            throw new \Tk\Auth\Exception('A table must be set in:  $config[`system.auth.dbTable.tableName`]');
        } elseif (!$options['system.auth.dbTable.usernameColumn']) {
            throw new \Tk\Auth\Exception('A table must be set in:  $config[`system.auth.dbTable.usernameColumn`]');
        } elseif (!$options['system.auth.dbTable.passwordColumn']) {
            throw new \Tk\Auth\Exception('A table must be set in:  $config[`system.auth.dbTable.passwordColumn`]');
        }
        parent::__construct($username, $password, $options);
    }



    /**
     * authenticate() - defined by Tk_Auth_Adapter_Interface.  This method is called to
     * attempt an authentication.  Previous to this call, this adapter would have already
     * been configured with all necessary information to successfully connect to a database
     * table and attempt to find a record matching the provided identity.
     *
     * @return \Tk\Auth\Result
     * @throws \Tk\Auth\Exception if answering the authentication query is impossible
     */
    public function authenticate()
    {
        if (!$this->getUsername() || !$this->getPassword()) {
            return $this->makeResult(\Tk\Auth\Result::FAILURE_CREDENTIAL_INVALID, 'Invalid account credentials.');
        }
        $db = $this->getConfig()->getDb();
        $where = '';
        if ($this->getOption('system.auth.dbTable.sqlWhere')) {
            $where = 'AND ' . $this->getOption('system.auth.dbTable.sqlWhere');
        }
        $sql = sprintf('SELECT * FROM `%s` WHERE `%s` = %s %s LIMIT 1',
            $this->getOption('system.auth.dbTable.tableName'),
            $this->getOption('system.auth.dbTable.usernameColumn'),
            enquote($this->getUsername()),
            $where);

        try {
            $result = $db->query($sql);
            $row = $result->fetch();
            if ($row) {
                $pasCol = $this->getOption('system.auth.dbTable.passwordColumn');
                $passHash = $this->getAuth()->hash($this->getPassword());
                if ($passHash == $row->$pasCol ) {
                    return $this->makeResult(\Tk\Auth\Result::SUCCESS);
                }
            }
        } catch (\Exception $e) {
            throw new \Tk\Auth\Exception('The supplied parameters failed to produce a valid sql statement, please check table and column names for validity.', 0, $e);
        }

        return $this->makeResult(\Tk\Auth\Result::FAILURE_CREDENTIAL_INVALID, 'Invalid username or password.');
    }


}
