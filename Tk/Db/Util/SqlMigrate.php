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


    public function __construct(Pdo $db, ?LoggerInterface $logger = null, string $table = '_migrate')
    {
        $this->db = $db;
        if (!$logger) $logger = new NullLogger();
        $this->logger = $logger;
        $this->table = $table;
    }

    public function __destruct()
    {
        $this->deleteBackup();
    }

    /**
     * Call this with a list of paths to search for migration files and execute each migration
     * in order they are supplied in the array
     * Returns an array of processed migrate files
     */
    public function migrateList(array $migrateList): array
    {
        $processed = [];
        $this->install();

        $list = $this->search($migrateList);

        foreach ($list as $k => $path) {
            if (is_file($path)) {
                if ($this->migrateFile($path)) {
                    $processed[$k] = $path;
                }
            }
        }

        return $processed;
    }

    /**
     * Execute a migration class or sql script...
     * the file is then added to the db and cannot be executed again.
     * Ignore any files starting with an underscore '_'
     */
    public function migrateFile(string $file): bool
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

            if (preg_match('/\.php$/i', basename($file))) {  // Include .php files
                $callback = include $file;
                if (is_callable($callback)) {
                    $callback($this->getDb());
                }
                $this->insertPath($file);
                return true;
            } else {  // is sql
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
                return true;
            }

        } catch (\Exception $e){
            $this->logger->error($e->__toString());
            //throw new \Tk\Exception('File: ' . $file, $e->getCode(), $e);
        }
        return false;
    }

    /**
     * Search for all migrate files in the pathList array of files/paths
     * Return a sorted flattened array that has the files that can be executed
     */
    protected function search(array $pathList): array
    {
        $found = [];
        foreach ($pathList as $path) {
            if (is_file($path) && preg_match('/.+\/([0-9]+)\.(php|sql)$/', $path, $regs)) {
                $found[$regs[1]] = $path;
            } else if (is_dir($path)) {
                $directory = new \RecursiveDirectoryIterator($path);
                $it = new \RecursiveIteratorIterator($directory);
                $regex = new \RegexIterator($it, '/.+\/([0-9]+)\.(php|sql)$/', \RegexIterator::GET_MATCH);
                foreach ($regex as $file) {
                    $found[$file[1] ?? '000000'] = $file[0];
                }
            }
        }

        ksort($found);
        return $found;
    }

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

    protected function deleteBackup(): void
    {
        if (is_writable($this->backupFile)) {
            unlink($this->backupFile);
            $this->backupFile = '';
        }
    }

    /**
     * install the migration table to cache executed scripts
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
     */
    protected function isInstall(): bool
    {
        if(!$this->getDb()->hasTable($this->getTable())) return true;
        $sql = sprintf('SELECT * FROM %s WHERE 1 LIMIT 1', $this->getDb()->quoteParameter($this->getTable()));
        $res = $this->getDb()->query($sql);
        if (!$res->rowCount()) return true;
        return false;
    }

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

    protected function insertPath(string $path): int
    {
        $this->logger->info("Migrating file: {$this->toRelative($path)}");
        $path = $this->getDb()->escapeString($this->toRelative($path));
        $rev = $this->getDb()->escapeString($this->toRev($path));
        $sql = sprintf('INSERT INTO %s (path, rev, created) VALUES (%s, %s, NOW())', $this->getDb()->quoteParameter($this->getTable()), $this->getDb()->quote($path), $this->getDb()->quote($rev));
        return $this->getDb()->exec($sql);
    }

    protected function deletePath(string $path): int
    {
        $path = $this->getDb()->escapeString($this->toRelative($path));
        $sql = sprintf('DELETE FROM %s WHERE path = %s LIMIT 1', $this->getDb()->quoteParameter($this->getTable()), $this->getDb()->quote($path));
        return $this->getDb()->exec($sql);
    }

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

    protected function getTable(): string
    {
        return $this->table;
    }

    public function getDb(): Pdo
    {
        return $this->db;
    }

}