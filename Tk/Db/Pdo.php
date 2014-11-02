<?php
/*
 * @author Tropotek <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Tropotek
 */
namespace Tk\Db;

/**
 * Database driver
 *
 * @package Tk\Db
 */
class Pdo extends \PDO
{

    static $logLastQuery = true;


    /**
     * The query time in seconds
     * @var int
     */
    public $queryTime = 0;

    /**
     * The total query time in seconds
     * @var int
     */
    public $totalQueryTime = 0;

    /**
     * @var string
     */
    public $lastQuery = '';

    /**
     * @var string
     */
    public $dbName = '';

    /**
     * @var string
     */
    public $driver = '';


    /**
     * Construct a \PDO SQL driver object
     *
     * @param string $dsn
     * @param string $username
     * @param string $passwd
     * @param string $options
     */
    public function __construct($dsn, $username, $passwd, $options = array())
    {
        if (!count($options)) {
            $options = array(
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
                \PDO::ATTR_STATEMENT_CLASS => array('\Tk\Db\PdoStatement', array())
            );
        }
        parent::__construct($dsn, $username, $passwd, $options);
        $regs = array();
        preg_match('/^([a-z]+):(([a-z]+)=([a-z0-9_-]+))+/i', $dsn, $regs);
        $this->dbName = $regs[4];
    }

    /**
     * query
     *
     * @param string $sql
     * @throws Exception
     * @return \PDOStatement
     */
    public function query($sql)
    {
        $this->setLastQuery($sql);
        try {
            $res = parent::query($sql);
            return $res;
        } catch (\Exception $e) {
            $ex = new Exception($e->getMessage(), $e->getCode(), $e);
            $ex->setDump($sql);
            throw $ex;
        }
    }

    /**
     * query
     *
     * @param string $sql
     * @throws Exception
     * @return int
     */
    public function exec($sql)
    {
        $this->setLastQuery($sql);
        try {
            $res =  parent::exec($sql);
            return $res;
        } catch (\Exception $e) {
            $ex = new Exception($e->getMessage(), $e->getCode(), $e);
            $ex->setDump($sql);
            throw $ex;
        }
    }

    /**
     * query
     *
     * @param string $sql
     * @param array $driver_options
     * @throws Exception
     * @return \PDOStatement
     */
    public function prepare($sql, $driver_options = array())
    {
        $this->setLastQuery($sql);
        try {
            $res =  parent::prepare($sql, $driver_options);
            return $res;
        } catch (\Exception $e) {
            $ex = new Exception($e->getMessage(), $e->getCode(), $e);
            $ex->setDump($sql);
            throw $ex;
        }
    }




    ///////////////////////////////////////////////////////////////////////////
    // \PDO - TK ADDITIONAL METHODS
    // TODO: Test for alternate drivers sqlLite, postgress, ODBC
    ///////////////////////////////////////////////////////////////////////////

    /**
     *
     * @param string $sql
     * @return self
     */
    protected function setLastQuery($sql)
    {
        if (self::$logLastQuery)
            $this->lastQuery = $sql;
        return $this;
    }

    /**
     * Get the last executed query.
     *
     * @return string
     */
    public function getLastQuery()
    {
        return $this->lastQuery;
    }

    /**
     * get the selected DB name
     *
     * @return string
     */
    public function getDbName()
    {
        return $this->dbName;
    }

    /**
     * Get the driver name
     *
     * @return string
     */
    public function getDriver()
    {
        return $this->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }

    /**
     * Execute a query and return a result object
     *
     * @param string $sqlBlock
     * @return bool
     */
    public function multiQuery($sql)
    {
        $sql = preg_replace("(--.*)", '', $sql);
        $queryList = preg_split('/\.*;\s*\n\s*/', $sql);
        if (!is_array($queryList) || count($queryList) == 0) {
            $e = new Exception('Error in SQL query data');
            throw $e;
        }
        foreach ($queryList as $query) {
            $query = trim($query);
            if (!$query) {
                continue;
            }
            $this->exec($query);
        }
    }

    /**
     * Count a query and return the total possible results
     *
     * @param string $sql
     * @return int
     */
    public function countQuery($sql = '')
    {
        if (!$sql) $sql = $this->getLastQuery();
        if (!$sql) return 0;


        self::$logLastQuery = false;
        $total = 0;
        if (preg_match('/^SELECT SQL_CALC_FOUND_ROWS/i', $sql)) {
            $countSql = 'SELECT FOUND_ROWS()';
            $result = $this->query($countSql);
            $result->setFetchMode(\PDO::FETCH_ASSOC);
            $row = $result->fetch();
            if ($row) {
                $total = (int) $row['FOUND_ROWS()'];
            }
        } else if (preg_match('/^SELECT/i', $sql)) {
            $cSql = preg_replace('/(LIMIT [0-9]+(( )?,?( )?(OFFSET )?[0-9]+)?)?/i', '', $sql);
            $countSql = "SELECT COUNT(*) as i FROM ($cSql) as t";
            $result = $this->query($countSql);
            $result->setFetchMode(\PDO::FETCH_ASSOC);
            $row = $result->fetch();
            if ($row) {
                $total = (int) $row['i'];
            }
        }
        self::$logLastQuery = true;
        return $total;
    }

    /**
     * Check if a database with the supplied name exists
     *
     * @param string $dbName
     * @return bool
     */
    public function databaseExists($dbName)
    {
        $dbName = $this->escapeString($dbName);
        $sql = sprintf("SHOW DATABASES LIKE '%s'", $dbName);
        $result = $this->query($sql);
        $result->setFetchMode(\PDO::FETCH_ASSOC);
        foreach ($result as $v) {
            $k = sprintf('Database (%s)', $dbName);
            if ($v[$k] == $dbName) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if a database with the supplied name exists
     *
     * @param string $tableName
     * @return bool
     */
    public function tableExists($table)
    {
        $table = $this->escapeString($table);
        $sql = sprintf("SHOW TABLES LIKE '%s'", $table);
        $result = $this->query($sql);
        $result->setFetchMode(\PDO::FETCH_ASSOC);
        foreach ($result as $v) {
            $k = sprintf('Tables_in_%s (%s)', $this->getDbName(), $table);
            if ($v[$k] == $table) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Get an array containing all the avalible databases to the user
     *
     * @return array
     */
    public function getDatabaseList()
    {
        $sql = "SHOW DATABASES";
        $result = $this->query($sql);
        $result->setFetchMode(\PDO::FETCH_ASSOC);
        $list = array();
        foreach ($result as $row) {
            $list[] = $row['Database'];
        }
        return $list;
    }

    /**
     * Get an array containing all the table names for this DB
     *
     * @return array
     */
    public function getTableList()
    {
        $sql = "SHOW TABLES";
        $result = $this->query($sql);
        $result->setFetchMode(\PDO::FETCH_NUM);
        $list = array();
        foreach ($result as $row) {
            $list[] = $row[0];
        }
        return $list;
    }

    /**
     * Get the insert id of the last added record.
     * Taken From: http://dev.mysql.com/doc/refman/5.0/en/innodb-auto-increment-handling.html
     *
     * @param string $tableName
     * @return int The next assigned integer to the primary key
     */
    public function getNextInsertId($table)
    {
        $table = $this->escapeString($table);
        $sql = sprintf("SHOW TABLE STATUS LIKE '%s' ", $table);
        $result = $this->query($sql);
        $result->setFetchMode(\PDO::FETCH_ASSOC);
        $row = $result->fetch();
        if ($row && isset($row['Auto_increment'])) {
            return (int)$row['Auto_increment'];
        }

        $sql = sprintf("SELECT MAX(`id`) AS `lastId` FROM `%s` ", $table);
        $result = $this->query($sql);
        $result->setFetchMode(\PDO::FETCH_ASSOC);
        $row = $result->fetch();
        return ((int) $row['lastId']) + 1;
    }
    
    /**
     * This function creates a temporary table filled with dates
     * This can be used in join querys for stats queries and ensures uniform date results
     * even if there is no data on that date.
     * <code>
     *   SELECT calDay.date AS DATE, SUM(orders.quantity) AS total_sales
     *     FROM orders RIGHT JOIN calDay ON (DATE(orders.order_date) = calDay.date)
     *   GROUP BY DATE
     * 
     * -- OR
     * 
     * SELECT DATE($cal.`date`) as 'date', IFNULL(count($tbl.`id`), 0) as 'total'
     * FROM `$tbl` RIGHT JOIN `$cal` ON (DATE($tbl.`created`) = DATE($cal.`date`) )
     * WHERE ($cal.`date` 
     *     BETWEEN (SELECT MIN(DATE(`created`)) FROM `$tbl`)
     *         AND (SELECT MAX(DATE(`created`)) FROM `$tbl`)
     * )
     * GROUP BY `date`
     * 
     * </code>
     * 
     * For interval info see ADDDATE() in the Mysql Manual.
     * @see http://dev.mysql.com/doc/refman/5.6/en/date-and-time-functions.html#function_date-add
     * 
     * @param \Tk\Date $dateFrom
     * @param \Tk\Date $dateTo
     * @param string $tableName
     * @param string $interval
     * @see http://www.richnetapps.com/using-mysql-generate-daily-sales-reports-filled-gaps/
     */
    public function createDateTable(\Tk\Date $dateFrom, \Tk\Date $dateTo, $tableName = 'calDay', $interval = '1 DAY')
    {
        $df = $dateFrom->toString('Y-m-d');
        $dt = $dateTo->toString('Y-m-d');
        
        $sql = <<<SQL
DROP TABLE IF EXISTS `$tableName`;
CREATE TABLE `$tableName` (`date` DATE );

DROP PROCEDURE IF EXISTS `fill_calendar`;
SQL;
        $this->multiQuery($sql);
        
        $sql = <<<SQL
CREATE PROCEDURE fill_calendar(start_date DATE, end_date DATE)
BEGIN
  DECLARE crt_date DATE;
  SET crt_date=start_date;
  WHILE crt_date < end_date DO
    INSERT INTO `$tableName` VALUES(crt_date);
    SET crt_date = ADDDATE(crt_date, INTERVAL $interval);
  END WHILE;
END
SQL;
        $st = $this->prepare($sql);
        $st->execute();
        
        
        $sql = <<<SQL
CALL fill_calendar('$df', '$dt');
SQL;
        $st = $this->prepare('CALL fill_calendar(?, ?)');
        $st->execute(array($df, $dt));
        
    }
    
    
    
    
    
    
    
    
    

    /**
     * Create a backup file of the database
     * The file will be named: [dbname]_yyyy-mm-dd_hh-mm.sql
     * The full filename and path will be returned.
     *
     * @param string $filepath
     * @param bool $dropTable
     * @return string
     */
    public function createBackup($filepath, $dropTable = true)
    {
        $dbname = $this->getDbName();
        // Open dump file
        $dumpfile = $filepath . '/' . $dbname . '_' . date('Y-m-d_H-i') . '.sql';
        vd($dumpfile);
        $fp = fopen($dumpfile, 'w');
        if (!is_resource($fp)) {
            throw new Exception('Backup failed: unable to open dump file: ' . $dumpfile);
        }

        // Header
        $date = date('r');
        $mysqlver = $this->getDriver() . ' ' . $this->getAttribute(\PDO::ATTR_SERVER_VERSION);
        $phpVer = phpversion();


        $out = <<<TEXT
-- SQL Dump
--
-- Generation: $date
-- MySQL version: $mysqlver
-- PHP version: $phpVer

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
-- SET time_zone = "+00:00";

--
-- Database: `$dbname`
--


TEXT;

        // Write
        fwrite($fp, $out);
        $out = '';

        // Fetch tables
        $tables = $this->query("SHOW TABLE STATUS");
        $c = 0;

        while ($table = $tables->fetch(\PDO::FETCH_ASSOC)) {
            $tableName = $table['Name'];
            $tmp = $this->query("SHOW CREATE TABLE `$tableName`");

            // Create table
            $create = $tmp->fetch(\PDO::FETCH_ASSOC);
            $out .= "\n\n--\n-- Table structure: `$tableName`\n--";
            if ($dropTable) {
                $out .= "\nDROP TABLE IF EXISTS `$tableName`;";
                $out .= "\n{$create['Create Table']};";
            }

            // Clean
            unset($tmp);

            // Write
            fwrite($fp, $out);
            $out = '';

            // Rows
            $tmp = $this->query("SHOW COLUMNS FROM `$tableName`");
            $rows = array();
            while ($row = $tmp->fetch(\PDO::FETCH_ASSOC)) {
                $rows[] = $row['Field'];
            }

            // Clean
            unset($tmp, $row);

            // Get data
            $tmp = $this->query("SELECT * FROM `$tableName`");
            $count = $tmp->rowCount();

            if ($count > 0) {
                $out .= "\n\n--\n-- Table data: `$tableName`\n--";
                $out .= "\nINSERT INTO `$tableName` (`" . implode('`, `', $rows) . "`) VALUES ";

                $i = 1;
                // Fetch data
                while ($entry = $tmp->fetch(\PDO::FETCH_ASSOC)) {

                    // Create values
                    $out .= "\n(";
                    $tmp2 = array();

                    foreach ($rows as $row) {
                        $tmp2[] = "'" . $this->escapeString($entry[$row]) . "'";
                    }

                    $out .= implode(', ', $tmp2);
                    $out .= $i++ === $count ? ');' : '),';

                    // Save
                    fwrite($fp, $out);
                    $out = '';
                }

                // Clean
                unset($tmp, $tmp2, $i, $count, $entry);
            }

            // Operations counter
            $c++;
        }
        unset($tables);
        // Close dump file
        fclose($fp);

        return $dumpfile;
    }

    /**
     * Restore a database backup file from a file
     *
     * @param string $filename
     * @return bool
     */
    public function restoreBackup($filename)
    {
        $handle = fopen($filename, "rb");
        if ($handle === false) {
            throw new Exception('Cannot open backup file: ' . $filename);
        }
        $buff = '';
        $i = -1;
        while (!feof($handle)) {
            $line = fgets($handle, 8192);
            $i++;
            if (substr(trim($line), 0, 2) == '--' || !trim($line)) {
                continue;
            }
            $buff .= $line;
            if (substr(trim($line), -1) == ';') {
                $this->query($buff);
                $buff = '';
            }
        }
        fclose($handle);
    }



    /**
     * Encode characters to avoid sql injections.
     *
     * @param string $str
     */
    public function escapeString($str)
    {
        if ($str) {
            return substr($this->quote($str), 1, -1);
        }
        return $str;
    }


}

/**
 * Tk\Db\PDOStatement
 *
 *
 * @package Tk\Db
 */
class PdoStatement extends \PDOStatement
{

  protected function __construct() { }

  // TODO: Add custom methods.

  // NOTE: I have delibratly left out adding count() and current()
  // As I do not want to confuse the SPL Iterator and this object.
  // We may review this in the future but for now use the \PDO methods
  // rowCount() and fetch() accordiningly.....




}

