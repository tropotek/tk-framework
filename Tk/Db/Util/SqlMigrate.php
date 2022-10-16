<?php
namespace Tk\Db\Util;


use Tk\Db\Pdo;

/**
 * DB migration tool
 *
 * TODO:
 *      So this all may need to be completly rewritten.
 *      Take the example from OUM's db migration where a change.sql file is
 *      saved to a log.sql and the change.sql is then deleted.
 *      DB migration is messy and never works as expected.
 *      each site should have the following files:
 *        - install.sql An up-to-date copy of the installable site
 *          The install could be auto generated if we use the comments to flag
 *          tables that should be save with their data. ??
 *        - A dump.sql.gz after each release stored somewhere in /data/temp ?
 *
 * A script that iterated the project files and executes *.sql files
 * once the files are executed they are then logs and not executed again.
 * Great for upgrading and installing a systems DB
 *
 * Files should reside in a folder named `.../sql/{type}/*`
 *
 * For a mysql file it could look like `.../sql/mysql/000001.sql`
 * for a postgress file `.../sql/pgsql/000001.sql`
 *
 * It is a good idea to start with a number to ensure that the files are
 * executed in the required order. Files found will be sorted alphabetically.
 *
 * <code>
 *   $migrate = new \Tk\Db\Migrate(Factory::getDb(), $this->config->getSitePath());
 *   $migrate->run()
 * </code>
 *
 * Migration files can be of type .sql or .php.
 * The php files are called with the include() command.
 * It will then be up to the developer to include a script to install the required sql.
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class SqlMigrate
{
    const MIGRATE_PREPEND = 'migrate_prepend';


    static string $DB_TABLE = '_migration';


    protected Pdo $db;

    protected string $sitePath = '';

    protected string $tempPath = '/tmp';

    /**
     * if empty a backup does not exist yet.
     */
    protected string $backupFile = '';


    /**
     * SqlMigrate constructor.
     *
     * @throws \Exception
     */
    public function __construct(Pdo $db, string $tempPath = '/tmp')
    {
        $this->sitePath = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__DIR__)))))));
        $this->tempPath = $tempPath;
        $this->setDb($db);
    }

    /**
     * Do any object cleanup
     */
    public function __destruct()
    {
        $this->deleteBackup();
    }

    /**
     * @param array|string[] $migrateList
     * @param null|callable $onStrWrite function(string $str, SqlMigrate $migrate) {}
     * @throws \Exception
     */
    public function migrateList($migrateList, $onStrWrite = null)
    {
        // IF no migration table exists or is empty
        // Run the install.sql and install.php if one is found
        if ($this->isInstall()) {
            foreach ($migrateList as $n => $searchPath) {
                if (!is_dir($searchPath)) continue;
                $dirItr = new \RecursiveDirectoryIterator($searchPath, \RecursiveIteratorIterator::CHILD_FIRST);
                $itr = new \RecursiveIteratorIterator($dirItr);
                $regItr = new \RegexIterator($itr, '/(install(\.sql|\.php))$/');
                /** @var \SplFileInfo $d */
                foreach ($regItr as $d) {
                    if ($this->migrateFile($d->getPathname())) {
                        if ($onStrWrite) call_user_func_array($onStrWrite, array($this->toRelative($d->getPathname()), $this));
                    }
                }
            }
        }

        if (!empty($migrateList[self::MIGRATE_PREPEND])) {
            $pre = $migrateList[self::MIGRATE_PREPEND];
            if (!is_array($pre)) $pre = array($pre);
            foreach ($pre as $n => $searchPath) {
                if (!is_dir($searchPath)) continue;
                $dirItr = new \RecursiveDirectoryIterator($searchPath, \RecursiveIteratorIterator::CHILD_FIRST);
                $itr = new \RecursiveIteratorIterator($dirItr);
                $regItr = new \RegexIterator($itr, '/(\.sql|\.php)$/');
                /** @var \SplFileInfo $d */
                foreach ($regItr as $d) {
                    if ($onStrWrite) call_user_func_array($onStrWrite, array('' . $d->getPath(), $this));
                    $this->migrate($d->getPath(), function ($f, $m) use ($onStrWrite) {
                        if ($onStrWrite) call_user_func_array($onStrWrite, array('  .' . $f, $m));
                    });
                }
            }
            unset($migrateList[self::MIGRATE_PREPEND]);
        }

        foreach ($migrateList as $n => $searchPath) {
            if (!is_dir($searchPath)) continue;
            $dirItr = new \RecursiveDirectoryIterator($searchPath, \RecursiveIteratorIterator::CHILD_FIRST);
            $itr = new \RecursiveIteratorIterator($dirItr);
            $regItr = new \RegexIterator($itr, '/(\/sql\/\.)$/');
                /** @var \SplFileInfo $d */
            foreach ($regItr as $d) {
                if ($onStrWrite) call_user_func_array($onStrWrite, array('' . $d->getPath(), $this));
                $this->migrate($d->getPath(), function ($f, $m) use ($onStrWrite) {
                    if ($onStrWrite) call_user_func_array($onStrWrite, array('  .' . $f, $m));
                });
            }
        }
    }

    /**
     * Run the migration script and find all non executed sql files
     *
     * @param string $path
     * @param null|callable $onFileMigrate
     * @return array
     * @throws \Exception
     */
    public function migrate($path, $onFileMigrate = null)
    {
        $list = $this->getFileList($path);
        $mlist = array();
        $sqlFiles = array();
        $phpFiles = array();

        try {
            // Find any migration files
            foreach ($list as $file) {
                if (preg_match('/\.php$/i', basename($file))) {   // Include .php files
                    $phpFiles[] = $file;
                } else {
                    $sqlFiles[] = $file;
                }
            }

            if (count($sqlFiles) || count($phpFiles)) {
                foreach ($sqlFiles as $file) {
                    if ($this->migrateFile($file)) {
                        if ($onFileMigrate) call_user_func_array($onFileMigrate, array($this->toRelative($file), $this));
                        $mlist[] = $this->toRelative($file);
                    }
                }
                foreach ($phpFiles as $file) {
                    if ($this->migrateFile($file)) {
                        if ($onFileMigrate) call_user_func_array($onFileMigrate, array($this->toRelative($file), $this));
                        $mlist[] = $this->toRelative($file);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->restoreBackup();
            throw new \Tk\Exception('Path: ' . $path, $e->getCode(), $e);
        }
        $this->deleteBackup();
        return $mlist;
    }

    /**
     * Check to see if there are any new migration sql files pending execution
     *
     * @param $path
     * @return bool
     * @throws \Tk\Db\Exception
     */
    public function isPending($path)
    {
        $list = $this->getFileList($path);
        $pending = false;
        foreach ($list as $file) {
            if (!$this->hasPath($file)) {
                $pending = true;
                break;
            }
        }
        return $pending;
    }

    /**
     * Set the temp path for db backup file
     * Default '/tmp'
     *
     * @param string $path
     * @return $this
     */
    public function setTempPath($path)
    {
        $this->tempPath = $path;
        return $this;
    }

    /**
     * search the path for *.sql files, also search the $path.'/'.$driver folder
     * for *.sql files.
     *
     * @param string $path
     * @return array
     */
    public function getFileList($path)
    {
        $list = array();
        $list = array_merge($list, $this->search($path));
        $list = array_merge($list, $this->search($path.'/'.$this->db->getDriver()));
        sort($list);
        return $list;
    }

    /**
     * Execute a migration class or sql script...
     * the file is then added to the db and cannot be executed again.
     * Ignore any files starting with an underscore '_'
     *
     * @param string $file
     * @return bool
     * @throws \Exception
     */
    protected function migrateFile($file)
    {
        try {
            $file = $this->sitePath . $this->toRelative($file);
            if (!is_readable($file)) return false;
            if ($this->hasPath($file)) return false;

            if (!$this->backupFile) {   // only run once per session.
                $dump = new SqlBackup($this->db);
                $this->backupFile = $dump->save($this->tempPath);   // Just in case
            }

            if (substr(basename($file), 0, 1) == '_') return false;

            if (preg_match('/\.php$/i', basename($file))) {         // Include .php files
                if (!trim(file_get_contents($file))) return false;

                if (is_file($file)) {
                    include($file);
                } else {
                    return false;
                }
                $this->insertPath($file);
            } else {                                                // is sql
                // replace any table prefix
                $sql = file_get_contents($file);
                if (!strlen(trim($sql))) return false;

                $stm = $this->db->prepare($sql);
                $stm->execute();

                // Bugger of a way to get the error:
                // https://stackoverflow.com/questions/23247553/how-can-i-get-an-error-when-running-multiple-queries-with-pdo
                $i = 0;
                do {
                    $i++;
                } while ($stm->nextRowset());
                $error = $stm->errorInfo();
                if ($error[0] != "00000") {
                    throw new \Tk\Db\Exception("Query $i failed: " . $error[2], 0, null, $sql);
                }
                $this->insertPath($file);
            }

        } catch (\Exception $e){
            throw new \Tk\Exception('File: ' . $file, $e->getCode(), $e);
        }
        return true;
    }

    /**
     * @param bool $deleteFile
     * @throws \Tk\Db\Exception
     * @throws \Tk\Exception
     */
    protected function restoreBackup($deleteFile = true)
    {
        if ($this->backupFile) {
            $dump = new SqlBackup($this->db);
            $dump->restore($this->backupFile);
            if ($deleteFile) {
                $this->deleteBackup();
            }
        }
    }

    /**
     * Delete the internally generated backup file if it exists
     */
    protected function deleteBackup()
    {
        if (is_writable($this->backupFile)) {
            unlink($this->backupFile);
            $this->backupFile = '';
        }
    }

    /**
     * Search a path for sql files
     *
     * @param $path
     * @return array
     */
    public function search($path)
    {
        $list = array();
        if (!is_dir($path)) return $list;
        $iterator = new \DirectoryIterator($path);
        foreach(new \RegexIterator($iterator, '/\.(php|sql)$/') as $file) {
            if (preg_match('/^(_|\.)/', $file->getBasename())) continue;
            if ($file->getBasename() == 'install.sql' || $file->getBasename() == 'install.php') continue;
            $list[] = $file->getPathname();
        }
        return $list;
    }

    /**
     * Get the table name for queries
     *
     * @return string
     */
    protected function getTable()
    {
        return self::$DB_TABLE;
    }

    /**
     * @return Pdo
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @param Pdo $db
     * @return $this
     * @throws \Tk\Db\Exception
     */
    public function setDb($db)
    {
        $this->db = $db;
        $this->install();
        return $this;
    }

    /**
     * install the migration table to track executed scripts
     *
     * @todo This must be tested against mysql, pgsql and sqlite....
     * So far query works with mysql and pgsql drvs sqlite still to test
     * @throws \Tk\Db\Exception
     */
    protected function install()
    {
        if($this->db->hasTable($this->getTable())) {
            return;
        }
        $tbl = $this->db->quoteParameter($this->getTable());
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS $tbl (
  path VARCHAR(128) NOT NULL DEFAULT '',
  created TIMESTAMP,
  PRIMARY KEY (path)
);
SQL;
        $this->db->exec($sql);
    }

    /**
     * Return true if the migration table is empty or does not exist
     *
     * @return bool
     * @throws \Tk\Db\Exception
     */
    protected function isInstall()
    {
        if(!$this->db->hasTable($this->getTable())) return true;
        $sql = sprintf('SELECT * FROM %s WHERE 1 LIMIT 1', $this->db->quoteParameter($this->getTable()));
        $res = $this->db->query($sql);
        if (!$res->rowCount()) return true;
        return false;
    }

    /**
     * exists
     *
     * @param string $path
     * @return bool
     * @throws \Tk\Db\Exception
     */
    protected function hasPath($path)
    {
        $path = $this->db->escapeString($this->toRelative($path));
        $sql = sprintf('SELECT * FROM %s WHERE path = %s LIMIT 1', $this->db->quoteParameter($this->getTable()), $this->db->quote($path));
        $res = $this->db->query($sql);
        if ($res->rowCount()) {
            return true;
        }
        return false;
    }

    /**
     * insert
     *
     * @param string $path
     * @return \PDOStatement
     * @throws \Tk\Db\Exception
     */
    protected function insertPath($path)
    {
        $path = $this->db->escapeString($this->toRelative($path));
        $sql = sprintf('INSERT INTO %s (path, created) VALUES (%s, NOW())', $this->db->quoteParameter($this->getTable()), $this->db->quote($path));
        return $this->db->exec($sql);
    }

    /**
     * delete
     *
     * @param string $path
     * @return \PDOStatement
     * @throws \Tk\Db\Exception
     */
    protected function deletePath($path)
    {
        $path = $this->db->escapeString($this->toRelative($path));
        $sql = sprintf('DELETE FROM %s WHERE path = %s LIMIT 1', $this->db->quoteParameter($this->getTable()), $this->db->quote($path));
        return $this->db->exec($sql);
    }

    /**
     * Return the relative path
     *
     * @param $path
     * @return string
     */
    private function toRelative($path)
    {
        return rtrim(str_replace($this->sitePath, '', $path), '/');
    }

}