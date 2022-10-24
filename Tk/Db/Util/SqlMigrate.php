<?php
namespace Tk\Db\Util;


use Tk\Db\Pdo;
use Tk\FileUtil;
use Tk\Traits\SystemTrait;

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
    use SystemTrait;

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
     * The site and vendor paths to check for migration files
     */
    protected array $searchPaths = [];


    /**
     * SqlMigrate constructor.
     *
     * @throws \Exception
     */
    public function __construct(Pdo $db, string $tempPath = '/tmp')
    {
        $this->sitePath = dirname(__DIR__, 7);
        $this->tempPath = $tempPath;
        $this->db = $db;

        // Get all searchable paths
        $basePath = $this->getConfig()->getBasePath();
        $vendorPath = $this->getSystem()->makePath($this->getConfig()->get('path.vendor.org'));
        $libPaths = scandir($vendorPath);
        array_shift($libPaths);
        array_shift($libPaths);
        $this->searchPaths = [
            $basePath . '/src/config'
        ] + array_map(fn($path) => $vendorPath . '/' . $path . '/config' , $libPaths);

    }

    /**
     * Do any object cleanup
     */
    public function __destruct()
    {
        $this->deleteBackup();
    }

    /**
     * @param callable|null $onStrWrite function(string $str, SqlMigrate $migrate) {}
     * @throws \Exception
     */
    public function migrateList(array $migrateList, callable $onStrWrite = null): void
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
            if (!is_array($pre)) $pre = [$pre];
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
     * @throws \Exception
     */
    public function migrate(string $path, callable $onFileMigrate = null): array
    {
        $this->install();

        $list = $this->getFileList($path);
        $mlist = [];
        $sqlFiles = [];
        $phpFiles = [];

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
     * @throws \Tk\Db\Exception
     */
    public function isPending(string $path): bool
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
     * search the path for *.sql files, also search the $path.'/'.$driver folder
     * for *.sql files.
     *
     * @param string $path
     * @return array
     */
    public function getFileList(string $path): array
    {
        $list = [];
        $list = array_merge($list, $this->search($path));
        $list = array_merge($list, $this->search($path.'/'.$this->db->getDriver()));
        sort($list);
        return $list;
    }

    /**
     * Search a path for sql files
     */
    public function search(string $path): array
    {
        $list = [];
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
     * Execute a migration class or sql script...
     * the file is then added to the db and cannot be executed again.
     * Ignore any files starting with an underscore '_'
     * @throws \Exception
     */
    protected function migrateFile(string $file): bool
    {
        try {
            $this->install();

            $file = $this->sitePath . $this->toRelative($file);
            if (!is_readable($file)) return false;
            if ($this->hasPath($file)) return false;

            if (!$this->backupFile) {   // only run once per session.
                $dump = new SqlBackup($this->db);
                $this->backupFile = $dump->save($this->tempPath);
            }

            if (str_starts_with(basename($file), '_')) return false;

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
     * @throws \Tk\Exception
     */
    protected function restoreBackup(bool $deleteFile = true): void
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
     * Set the temp path for db backup file
     * Default '/tmp'
     */
    public function setTempPath(string $path): static
    {
        $this->tempPath = $path;
        return $this;
    }

    /**
     * Delete the internally generated backup file if it exists
     */
    protected function deleteBackup(): void
    {
        if (is_writable($this->backupFile)) {
            unlink($this->backupFile);
            $this->backupFile = '';
        }
    }

    /**
     * Get the migration table name
     */
    protected function getTable(): string
    {
        return self::$DB_TABLE;
    }

    public function getDb(): Pdo
    {
        return $this->db;
    }

    /**
     * install the migration table to track executed scripts
     *
     * @throws \Tk\Db\Exception
     */
    protected function install(): void
    {
        if($this->db->hasTable($this->getTable())) {
            return;
        }
        $tbl = $this->db->quoteParameter($this->getTable());
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS $tbl (
  path VARCHAR(128) NOT NULL DEFAULT '',
  rev VARCHAR(16) NOT NULL DEFAULT '',
  created TIMESTAMP,
  PRIMARY KEY (path)
);
SQL;
        $this->db->exec($sql);
    }

    /**
     * Return true if the migration table is empty or does not exist
     * @throws \Tk\Db\Exception
     */
    protected function isInstall(): bool
    {
        if(!$this->db->hasTable($this->getTable())) return true;
        $sql = sprintf('SELECT * FROM %s WHERE 1 LIMIT 1', $this->db->quoteParameter($this->getTable()));
        $res = $this->db->query($sql);
        if (!$res->rowCount()) return true;
        return false;
    }

    /**
     * exists
     * @throws \Tk\Db\Exception
     */
    protected function hasPath(string $path): bool
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
     * @throws \Tk\Db\Exception
     */
    protected function insertPath(string $path): int
    {
        $path = $this->db->escapeString($this->toRelative($path));
        $rev = $this->db->escapeString($this->toRev($path));
        $sql = sprintf('INSERT INTO %s (path, rev, created) VALUES (%s, %s, NOW())', $this->db->quoteParameter($this->getTable()), $this->db->quote($path), $this->db->quote($rev));
        return $this->db->exec($sql);
    }

    /**
     * delete
     * @throws \Tk\Db\Exception
     */
    protected function deletePath(string $path): int
    {
        $path = $this->db->escapeString($this->toRelative($path));
        $sql = sprintf('DELETE FROM %s WHERE path = %s LIMIT 1', $this->db->quoteParameter($this->getTable()), $this->db->quote($path));
        return $this->db->exec($sql);
    }

    /**
     * Return the relative path
     */
    private function toRelative(string $path): string
    {
        return rtrim(str_replace($this->sitePath, '', $path), '/');
    }

    /**
     * Return the revision string part of the path
     */
    private function toRev(string $path): string
    {
        $path = basename($path);
        return FileUtil::removeExtension($path);
    }

}