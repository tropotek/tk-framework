<?php
namespace Tk\Db;

use Tk\Config;
use Tk\FileUtil;
use Tk\Db;

/**
 * A utility to back up and restore a DB in PHP.
 *
 * @note: This file uses SLI commands to back up and restore the database
 * @see https://raw.githubusercontent.com/kakhavk/database-dump-utility/master/SqlDump.php
 */
class DbBackup
{

    private \PDO $db;


    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    protected function getDb(): \PDO
    {
        return $this->db;
    }

    /**
     * Restore an sql file
     */
    public function restore(string $sqlFile, array $options = []): void
    {
        if (!is_readable($sqlFile)) return;

        // Un-compress file if required
        if (preg_match('/^(.+)\.gz$/', $sqlFile, $regs)) {
            if (is_file($regs[1])) @unlink($regs[1]);

            $command = sprintf('gunzip %s', escapeshellarg($sqlFile));
            exec($command, $out, $ret);
            if ($ret != 0) throw new Exception(implode("\n", $out));

            $sqlFile = $regs[1];
        }

        $dsn = Db::parseDsn(Config::instance()->get('db.mysql'));

        // In short, the new MariaDB version adds this line to the beginning of the dump file:
        //  /*!999999\- enable the sandbox mode */
        // Replace "/*!999999\- enable the sandbox mode */" string on first line if exists
        // https://gorannikolovski.com/blog/mariadb-import-issue-error-at-line-1-unknown-command
        $f = fopen($sqlFile, 'r');
        $line = fgets($f);
        if (str_contains($line, '/*!999999\- enable the sandbox mode */')) {
            $contents = file($sqlFile);
            array_shift($contents);
            file_put_contents($sqlFile, implode("\r\n", $contents));
        }
        fclose($f);

        // todo: add the db port to the command
        $command = sprintf('mysql %s --port=%s -h %s -u %s -p%s < %s',
            escapeshellarg($dsn['dbName']),
            $dsn['port'] ?? 0,
            escapeshellarg($dsn['host']),
            escapeshellarg($dsn['user']),
            escapeshellarg($dsn['pass']),
            escapeshellarg($sqlFile)
        );
        exec($command, $out, $ret);

        if ($ret != 0) throw new Exception(implode("\n", $out));
    }

    /**
     * Save the sql to a path.
     *
     * If no file is supplied then the default file name is used: {DbName}_2016-01-01-12-00-00.sql
     * if the path does not already contain a .sql file extension
     */
    public function save(string $path = '', array $options = []): string
    {
        $sqlFile = $path;
        $dsn = Db::parseDsn(Config::instance()->get('db.mysql'));

        if (!preg_match('/\.sql$/', $sqlFile)) {
            $path = rtrim($path, '/');
            FileUtil::mkdir($path);

            if (!is_writable($path)) throw new Exception('Cannot access path: ' . $path);

            $file = $dsn['dbName'] . "_" . date("Y-m-d-H-i-s").".sql";
            $sqlFile = $path.'/'.$file;
        }

        $exclude = $exclude ?? [];
        if (!in_array(Config::instance()->get('session.db_table', ''), $exclude)) {
            $exclude[] = Config::instance()->get('session.db_table');
        }
        // Exclude all views
        $sql = "SHOW FULL TABLES IN `{$dsn['dbName']}` WHERE TABLE_TYPE LIKE 'VIEW';";
        $result = $this->getDb()->query($sql);
        while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
            $v = array_shift($row);
            $exclude[] = $v;
        }

        $excludeParams = [];
        foreach ($exclude as $tbl) {
            $excludeParams[] = "--ignore-table={$dsn['dbName']}.$tbl";
        }
        $command = sprintf('mysqldump --skip-triggers --max_allowed_packet=1G --single-transaction --quick --lock-tables=false %s --opt --port=%s -h %s -u %s -p%s %s > %s',
            implode(' ', $excludeParams),
            $dsn['port'] ?? 0,
            escapeshellarg($dsn['host']),
            escapeshellarg($dsn['user']),
            escapeshellarg($dsn['pass']),
            escapeshellarg($dsn['dbName']),
            escapeshellarg($sqlFile)
        );
        exec($command, $out, $ret);

        if ($ret != 0) throw new Exception(implode("\n", $out));
        if(filesize($sqlFile) <= 0) throw new Exception('Size of file '.$sqlFile.' is ' . filesize($sqlFile));

        return $sqlFile;
    }

    /**
     * @throws Exception
     * @note: This could have memory issues with large databases, use SqlBackup:save() in those cases
     */
    public function dump(array $options = []): string
    {
        $dsn = Db::parseDsn(Config::instance()->get('db.mysql'));

        $exclude = $exclude ?? [];
        if (!in_array(Config::instance()->get('session.db_table', ''), $exclude)) {
            $exclude[] = Config::instance()->get('session.db_table');
        }
        // Exclude all views
        $sql = "SHOW FULL TABLES IN `{$dsn['dbName']}` WHERE TABLE_TYPE LIKE 'VIEW';";
        $result = $this->getDb()->query($sql);
        while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
            $v = array_shift($row);
            $exclude[] = $v;
        }

        $excludeParams = [];
        foreach ($exclude as $tbl) {
            $excludeParams[] = "--ignore-table={$dsn['dbName']}.$tbl";
        }
        $command = sprintf('mysqldump --max_allowed_packet=1G --single-transaction --quick --lock-tables=false %s --opt --port=%s -h %s -u %s -p%s %s',
            implode(' ', $excludeParams),
            escapeshellarg($dsn['host']),
            $dsn['port'] ?? 0,
            escapeshellarg($dsn['user']),
            escapeshellarg($dsn['pass']),
            escapeshellarg($dsn['dbName'])
        );

        exec($command, $out, $ret);

        if ($ret != 0) throw new Exception(implode("\n", $out));

        return implode("\n", $out);
    }

}