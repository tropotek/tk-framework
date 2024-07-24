<?php
namespace Tk\Db\Util;

use Tk\Db\Pdo;
use Tk\Db\Exception;
use Tk\FileUtil;
use Tk\Traits\SystemTrait;

/**
 * A utility to backup and restore a DB in PHP.
 *
 * @note: This file uses SLI commands to backup and restore the database
 * @see https://raw.githubusercontent.com/kakhavk/database-dump-utility/master/SqlDump.php
 */
class SqlBackup
{
    use SystemTrait;

    /**
     * @var Pdo
     */
    private Pdo $db;


    public function __construct(Pdo $db)
    {
        $this->db = $db;
    }

    protected function getDb(): Pdo
    {
        return $this->db;
    }

    /**
     * Restore an sql file
     *
     * @throws Exception
     * @throws \Tk\Exception
     */
    public function restore(string $sqlFile, array $options = [])
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

        $options = ($this->getDb()->getOptions() + $options);
        extract($options);

        $command = '';
        if ('mysql' == $this->db->getDriver()) {
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

            $command = sprintf('mysql %s -h %s -u %s -p%s < %s', $name, $host, $user, $pass, escapeshellarg($sqlFile));
        } else {
            throw new \Tk\Exception('Only mysql driver supported');
        }
        exec($command, $out, $ret);
        if ($ret != 0) throw new Exception(implode("\n", $out));
    }

    /**
     * Save the sql to a path.
     *
     * If no file is supplied then the default file name is used: {DbName}_2016-01-01-12-00-00.sql
     * if the path does not already contain a .sql file extension
     *
     * @throws Exception
     */
    public function save(string $path = '', array $options = []): string
    {
        $sqlFile = $path;
        if (!preg_match('/\.sql$/', $sqlFile)) {
            $path = rtrim($path, '/');
            FileUtil::mkdir($path);

            if (!is_writable($path)) throw new Exception('Cannot access path: ' . $path);

            $file = $this->getDb()->getDatabaseName() . "_" . $this->getDb()->getDriver() . "_" . date("Y-m-d-H-i-s").".sql";
            $sqlFile = $path.'/'.$file;
        }

        $options = ($this->getDb()->getOptions() + $options);
        extract($options);

        $command = '';
        if ('mysql' == $this->getDb()->getDriver()) {
            $exclude = $exclude ?? [];
            if (!in_array($this->getConfig()->get('session.db_table', ''), $exclude)) {
                $exclude[] = $this->getConfig()->get('session.db_table');
            }
            // Exclude all views
            $sql = "SHOW FULL TABLES IN `{$name}` WHERE TABLE_TYPE LIKE 'VIEW';";
            $result = $this->getDb()->query($sql);
            while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
                $v = array_shift($row);
                $exclude[] = $v;
            }

            $excludeParams = [];
            foreach ($exclude as $tbl) {
                $excludeParams[] = "--ignore-table={$name}.$tbl";
            }
            $command = sprintf('mysqldump --skip-triggers --max_allowed_packet=1G --single-transaction --quick --lock-tables=false %s --opt -h %s -u %s -p%s %s > %s', implode(' ', $excludeParams), $host, $user, $pass, $name, escapeshellarg($sqlFile));
        } else {
            throw new \Tk\Exception('Only mysql driver supported');
        }

        if(!$command) throw new Exception('Database driver not supported:  ' . $this->getDb()->getDriver());

        exec($command, $out, $ret);

        if ($ret != 0) throw new Exception(implode("\n", $out));
        if(filesize($sqlFile) <= 0) throw new Exception('Size of file '.$sqlFile.' is ' . filesize($sqlFile));

        return $sqlFile;
    }

    /**
     *
     * @throws Exception
     * @note: This could have memory issues with large databases, use SqlBackup:save() in those cases
     */
    public function dump(array $options = []): string
    {
        $options = ($this->getDb()->getOptions() + $options);
        extract($options);

        $command = '';
        if ('mysql' == $this->getDb()->getDriver()) {
            $exclude = $exclude ?? [];
            if (!in_array($this->getConfig()->get('session.db_table', ''), $exclude)) {
                $exclude[] = $this->getConfig()->get('session.db_table');
            }
            // Exclude all views
            $sql = "SHOW FULL TABLES IN `{$name}` WHERE TABLE_TYPE LIKE 'VIEW';";
            $result = $this->getDb()->query($sql);
            while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
                $v = array_shift($row);
                $exclude[] = $v;
            }

            $excludeParams = [];
            foreach ($exclude as $tbl) {
                $excludeParams[] = "--ignore-table={$name}.$tbl";
            }
            $command = sprintf('mysqldump --max_allowed_packet=1G --single-transaction --quick --lock-tables=false %s --opt -h %s -u %s -p%s %s', implode(' ', $excludeParams), $host, $user, $pass, $name);
        } else {
            throw new \Tk\Exception('Only mysql driver supported');
        }

        if(!$command) throw new Exception('Database driver not supported: ' . $this->getDb()->getDriver());

        exec($command, $out, $ret);

        if ($ret != 0) throw new Exception(implode("\n", $out));

        return implode("\n", $out);
    }

}