<?php
namespace Tk\Db\Util;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Tk\Collection;
use Tk\Db\Pdo;
use Tk\FileUtil;
use Tk\Traits\SystemTrait;

/**
 * DB migration tool
 *
 * It is a good idea to start with a number to ensure that the files are
 * executed in the required order. Files found will be sorted alphabetically.
 *
 * <code>
 *   $migrate = new Migrate(Factory::instance()->getDb());
 *   $migrate->migrateList([]);
 * </code>
 *
 * Migration files can be of type .sql or .php.
 * The php files are called with the include() command
 * and the php file should return a closure like the following:
 * <code>
 *  return function (Tk\Db\Pdo $db) {
 *      ...
 *  };
 * </code>
 */
class SqlMigrate
{
    use SystemTrait;


    protected Pdo $db;

    protected string $table = '';

    protected string $backupFile = '';

    protected LoggerInterface $logger;

    private array $foundFiles = [];


    /**
     * @throws \Exception
     */
    public function __construct(Pdo $db, ?LoggerInterface $logger = null, string $table = '_migrate')
    {
        $this->db = $db;
        if (!$logger) $logger = new NullLogger();
        $this->logger = $logger;
        $this->table = $table;
    }

    /**
     * Do any object cleanup
     */
    public function __destruct()
    {
        $this->deleteBackup();
    }

    /**
     * Call this with a list of paths to search for migration files and execute each migration
     * in order they are supplied in the array
     * @throws \Exception
     */
    public function migrateList(array $migrateList): array
    {
        $list = [];
        $this->install();

        foreach ($migrateList as $path) {
            if (is_file($path)) {
                $this->migrateFile($path);
                $list[] = $path;
            } else {
                $list += $this->migratePath($path);
            }
        }

        return $list;
    }

    /**
     * Run the migration script and find all non executed sql files within the path
     * @throws \Exception
     */
    public function migratePath(string $path): array
    {
        try {
            $this->install();

            $list = $this->search($path);

            // Find any migration files
            foreach ($list as $file) {
                $this->migrateFile($file);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->__toString());
            $this->restoreBackup();
            throw new \Tk\Exception('Path: ' . $path, $e->getCode(), $e);
        }
        $this->deleteBackup();

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

            $file = $this->getConfig()->getBasePath() . $this->toRelative($file);
            if (!is_readable($file)) return false;
            if ($this->hasPath($file)) return false;


            if (!$this->backupFile) {   // only run once per session.
                $dump = new SqlBackup($this->getDb());
                $this->backupFile = $dump->save($this->getConfig()->getTempPath());
            }

            if (preg_match('/\.php$/i', basename($file))) {         // Include .php files
                $callback = include $file;
                if (is_callable($callback)) {
                    $callback($this->getDb());
                }
                $this->insertPath($file);
            } else {                                                // is sql
                // replace any table prefix
                $sql = file_get_contents($file);
                if (!strlen(trim($sql))) return false;

                $stm = $this->getDb()->prepare($sql);
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
            $this->logger->error($e->__toString());
            //throw new \Tk\Exception('File: ' . $file, $e->getCode(), $e);
        }
        return true;
    }

    /**
     * Check to see if there are any new migration sql files pending execution
     * @throws \Tk\Db\Exception
     */
    public function isPending(string $path): bool
    {
        $list = $this->search($path);
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
     * Search a path for sql files
     */
    public function search(string $path): array
    {
        if (!array_key_exists($path, $this->foundFiles)) {
            $list = [];
            if (!is_dir($path)) return $list;
            $directory = new \RecursiveDirectoryIterator($path);
            $it = new \RecursiveIteratorIterator($directory);
            $regex = new \RegexIterator($it, '/.+\/([0-9]+)\.(php|sql)$/', \RegexIterator::GET_MATCH);
            foreach ($regex as $file) {
                $list[$file[1] ?? '000000'][] = $file[0];
            }
            ksort($list);
            $this->foundFiles[$path] = Collection::arrayFlatten($list);
        }
        return $this->foundFiles[$path];
    }

    /**
     * @throws \Tk\Exception
     */
    protected function restoreBackup(bool $deleteFile = true): void
    {
        if ($this->backupFile) {
            $dump = new SqlBackup($this->getDb());
            $dump->restore($this->backupFile);
            if ($deleteFile) {
                $this->deleteBackup();
            }
        }
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
     * install the migration table to track executed scripts
     * @throws \Tk\Db\Exception
     */
    protected function install(): void
    {
        if($this->getDb()->hasTable($this->getTable())) {
            return;
        }
        $tbl = $this->getDb()->quoteParameter($this->getTable());
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS $tbl (
  path VARCHAR(128) NOT NULL DEFAULT '',
  rev VARCHAR(16) NOT NULL DEFAULT '',
  created TIMESTAMP,
  PRIMARY KEY (path)
);
SQL;
        $this->getDb()->exec($sql);
    }

    /**
     * Return true if the migration table is empty or does not exist
     * @throws \Tk\Db\Exception
     */
    protected function isInstall(): bool
    {
        if(!$this->getDb()->hasTable($this->getTable())) return true;
        $sql = sprintf('SELECT * FROM %s WHERE 1 LIMIT 1', $this->getDb()->quoteParameter($this->getTable()));
        $res = $this->getDb()->query($sql);
        if (!$res->rowCount()) return true;
        return false;
    }

    /**
     * exists
     * @throws \Tk\Db\Exception
     */
    protected function hasPath(string $path): bool
    {
        $path = $this->getDb()->escapeString($this->toRelative($path));
        $sql = sprintf('SELECT * FROM %s WHERE path = %s LIMIT 1', $this->getDb()->quoteParameter($this->getTable()), $this->getDb()->quote($path));
        $res = $this->getDb()->query($sql);
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
        vd($path);
        $this->logger->info("Migrating file: {$this->toRelative($path)}");
        $path = $this->getDb()->escapeString($this->toRelative($path));
        $rev = $this->getDb()->escapeString($this->toRev($path));
        $sql = sprintf('INSERT INTO %s (path, rev, created) VALUES (%s, %s, NOW())', $this->getDb()->quoteParameter($this->getTable()), $this->getDb()->quote($path), $this->getDb()->quote($rev));
        return $this->getDb()->exec($sql);
    }

    /**
     * delete
     * @throws \Tk\Db\Exception
     */
    protected function deletePath(string $path): int
    {
        $path = $this->getDb()->escapeString($this->toRelative($path));
        $sql = sprintf('DELETE FROM %s WHERE path = %s LIMIT 1', $this->getDb()->quoteParameter($this->getTable()), $this->getDb()->quote($path));
        return $this->getDb()->exec($sql);
    }

    /**
     * Return the relative path
     */
    private function toRelative(string $path): string
    {
        return rtrim(str_replace($this->getConfig()->getBasePath(), '', $path), '/');
    }

    /**
     * Return the revision string part of the path
     */
    private function toRev(string $path): string
    {
        $path = basename($path);
        return FileUtil::removeExtension($path);
    }

    /**
     * Get the migration table name
     */
    protected function getTable(): string
    {
        return $this->table;
    }

    public function getDb(): Pdo
    {
        return $this->db;
    }

}