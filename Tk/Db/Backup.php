<?php
namespace Tk\Db;

/**
 * Class Backup
 *
 * Backup and restore function for a full MySQL database
 *
 * @author Tropotek <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Tropotek
 */
class Backup
{

    /**
     * @var Pdo
     */
    private $db = null;



    /**
     * construct
     *
     * @param Pdo $db
     */
    function __construct(Pdo $db)
    {
        $this->db = $db;
    }


    /**
     * Create a backup file of the database
     * The full filename and path will be returned.
     *
     * @param string $outFile
     * @param bool   $dropTable
     * @return string
     * @throws Exception
     */
    public function export($outFile, $dropTable = true)
    {
        $dbName = $this->db->getDbName();
        $fp = fopen($outFile, 'w');
        if (!is_resource($fp)) {
            throw new Exception('Unable to open file for writing: ' . $outFile);
        }

        // Header
        $date = date('r');
        $mysqlVer = $this->db->getDriver() . ' ' . $this->db->getAttribute(\PDO::ATTR_SERVER_VERSION);
        $phpVer = phpversion();

        $out = <<<TEXT
-- SQL Dump
--
-- Generation: $date
-- MySQL version: $mysqlVer
-- PHP version: $phpVer

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
-- SET time_zone = "+00:00";

--
-- Database: `$dbName`
--

TEXT;

        // Write
        fwrite($fp, $out);
        $out = '';

        // Fetch tables
        $tables = $this->db->query("SHOW TABLE STATUS");
        $c = 0;

        while ($table = $tables->fetch(\PDO::FETCH_ASSOC)) {
            $tableName = $table['Name'];
            $tmp = $this->db->query("SHOW CREATE TABLE `$tableName`");

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
            $tmp = $this->db->query("SHOW COLUMNS FROM `$tableName`");
            $rows = array();
            while ($row = $tmp->fetch(\PDO::FETCH_ASSOC)) {
                $rows[] = $row['Field'];
            }

            // Clean
            unset($tmp, $row);

            // Get data
            $tmp = $this->db->query("SELECT * FROM `$tableName`");
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
                        $tmp2[] = $this->db->quote($entry[$row]);
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

        return $outFile;
    }



    /**
     * Restore a database backup file from a file
     *
     * @param string $inFile
     * @return bool
     * @throws Exception
     */
    public function import($inFile)
    {
        $handle = fopen($inFile, "rb");
        if ($handle === false) {
            throw new Exception('Cannot open file: ' . $inFile);
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
                $this->db->query($buff);
                $buff = '';
            }
        }
        fclose($handle);
    }



}