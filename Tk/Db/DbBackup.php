<?php
namespace Tk\Db;

use Tk\Config;
use Tk\FileUtil;
use Tk\Db;
use Tk\Log;

/**
 * A utility to back up and restore a DB in PHP.
 *
 * @note: This file uses CLI commands to back up and restore the database
 * @see https://raw.githubusercontent.com/kakhavk/database-dump-utility/master/SqlDump.php
 */
class DbBackup
{
    public static string $error = '';

    /**
     * Restore a sql file
     *
     * $options = [
     *       'host' => 'localhost',
     *       'port' => 0,
     *       'user' => 'username',
     *       'pass' => 'password',
     *       'dbName' => 'database-name',
     * ]
     */
    public static function restore(string $sqlFile, array $options = []): bool
    {
        self::$error = '';

        if (!is_readable($sqlFile)) {
            self::$error = 'File not found: ' . $sqlFile;;
            return false;
        }

        // Un-compress file if required
        if (preg_match('/^(.+)\.gz$/', $sqlFile, $regs)) {
            if (is_file($regs[1])) @unlink($regs[1]);

            $command = sprintf('gunzip %s', escapeshellarg($sqlFile));
            exec($command, $out, $ret);
            if ($ret != 0) throw new Exception(implode("\n", $out));

            $sqlFile = $regs[1];
        }

        // In short, the new MariaDB version adds this line to the beginning of the dump file:
        //  /*!999999\- enable the sandbox mode */
        // Replace "/*!999999\- enable the sandbox mode */" string on first line if exists
        // https://gorannikolovski.com/blog/mariadb-import-issue-error-at-line-1-unknown-command
        $f = fopen($sqlFile, 'r');
        if ($f === false) {
            self::$error = 'Cannot open file ' . $sqlFile;
            return false;
        }

        $line = fgets($f);
        if (str_contains(strval($line), '/*!999999\- enable the sandbox mode */')) {
            $contents = file($sqlFile);
            if (is_array($contents)) {
                array_shift($contents);
                file_put_contents($sqlFile, implode("\r\n", $contents));
            }
        }
        fclose($f);

        $command = sprintf('mysql %s --port=%s -h %s -u %s -p%s < %s',
            escapeshellarg($options['dbName']),
            $options['port'] ?? 0,
            escapeshellarg($options['host']),
            escapeshellarg($options['user']),
            escapeshellarg($options['pass']),
            escapeshellarg($sqlFile)
        );
        exec($command, $out, $ret);

        if ($ret != 0) {
            self::$error = implode("\n", $out);
            return false;
        }

        return true;
    }

    /**
     * @note: This saves the dump to a string in memory, use SqlBackup::save() for large databases
     */
    public static function dump(array $options = []): string
    {
        $exclude = $options['exclude'] ?? [];
        if (!in_array(Config::instance()->get('session.db_table', ''), $exclude)) {
            $exclude[] = Config::instance()->get('session.db_table');
        }
        // Exclude all views
        if (is_null(Db::getPdo())) {
            Db::connect(Db::toDsn($options));
        }
        $sql = "SHOW FULL TABLES IN `{$options['dbName']}` WHERE TABLE_TYPE LIKE 'VIEW'";
        $result = Db::getPdo()->query($sql);
        if ($result !== false) {
            while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
                $v = array_shift($row);
                $exclude[] = $v;
            }
        }

        $excludeParams = [];
        foreach ($exclude as $tbl) {
            $excludeParams[] = "--ignore-table={$options['dbName']}.$tbl";
        }
        $command = sprintf('mysqldump --max_allowed_packet=1G --single-transaction --quick --lock-tables=false %s --opt --port=%s -h %s -u %s -p%s %s',
            implode(' ', $excludeParams),
            $options['port'] ?? 0,
            escapeshellarg($options['host']),
            escapeshellarg($options['user']),
            escapeshellarg($options['pass']),
            escapeshellarg($options['dbName'])
        );
        exec($command, $out, $ret);

        if ($ret != 0) {
            self::$error = implode("\n", $out);
            return '';
        }

        return implode("\n", $out);
    }

    /**
     * Save the sql to a path.
     * Use this for large databases to avoid storing the SQL dump in memory.
     *
     * If no file is supplied then the default file name is used: {DbName}_2016-01-01-12-00-00.sql
     * if the path does not already contain a .sql file extension
     * $options = [
     *       'host' => 'localhost',
     *       'port' => 0,
     *       'user' => 'username',
     *       'pass' => 'password',
     *       'dbName' => 'db_name',
     * ]
     */
    public static function save(string $path = '', array $options = []): bool
    {
        $sqlFile = $path;

        if (!preg_match('/\.sql$/', $sqlFile)) {
            $path = rtrim($path, '/');
            FileUtil::mkdir($path);

            if (!is_writable($path)) {
                self::$error = "Cannot write to $path";
                return false;
            }

            $file = $options['dbName'] . "_" . date("Y-m-d-H-i-s").".sql";
            $sqlFile = $path.'/'.$file;
        }

        $exclude = $options['exclude'] ?? [];
        if (!in_array(Config::instance()->get('session.db_table', ''), $exclude)) {
            $exclude[] = Config::instance()->get('session.db_table');
        }
        // Exclude all views
        if (is_null(Db::getPdo())) {
            Db::connect(Db::toDsn($options));
        }
        $sql = "SHOW FULL TABLES IN `{$options['dbName']}` WHERE TABLE_TYPE LIKE 'VIEW'";
        $result = Db::getPdo()->query($sql);
        if ($result !== false) {
            while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
                $v = array_shift($row);
                $exclude[] = $v;
            }
        }

        $excludeParams = [];
        foreach ($exclude as $tbl) {
            $excludeParams[] = "--ignore-table={$options['dbName']}.$tbl";
        }

        $command = sprintf('mysqldump --skip-triggers --max_allowed_packet=1G --single-transaction --quick --lock-tables=false %s --opt --port=%s -h %s -u %s -p%s %s > %s',
            implode(' ', $excludeParams),
            $options['port'] ?? 0,
            escapeshellarg($options['host']),
            escapeshellarg($options['user']),
            escapeshellarg($options['pass']),
            escapeshellarg($options['dbName']),
            escapeshellarg($sqlFile)
        );
        exec($command, $out, $ret);

        if ($ret != 0) {
            self::$error = implode("\n", $out);
            return false;
        }
        if (intval(filesize($sqlFile)) == 0) {
            self::$error = 'File not found: ' . $sqlFile;
        }

        return true;
    }

}