<?php
namespace Tk\Db;

use Tk\Traits\ConfigTrait;
use Tk\Traits\FactoryTrait;
use Tk\Traits\SystemTrait;

/**
 * A collection object that can store its values in a DB table.
 * This replaces the old Data store object
 *
 * The main difference to the Collection object is that you will need
 * to supply a table name and PDO object to create an instance.
 *
 * After you have made your modifications you must call the Collection->save();
 * method so the data is saved from memory to the DB table.
 */
class Collection extends \Tk\Collection
{
    use SystemTrait;


    protected string $table = '';

    private array $del = [];

    private ?\PDO $pdo = null;


    public function __construct(string $table, \PDO $pdo = null)
    {
        parent::__construct();
        $this->table = $table;
        $this->pdo = $pdo;
        if (is_null($this->pdo)) {
            $this->pdo = $this->getFactory()->getDb()->getPdo();
        }
    }

    public function __sleep()
    {
        return ['table', 'del'];
    }

    public function __wakeup()
    {
        $this->pdo = $this->getFactory()->getDb()->getPdo();
    }

    public function getPdo(): \PDO
    {
        return $this->pdo;
    }

    public function setPdo(\PDO $pdo): static
    {
        $this->pdo = $pdo;
        return $this;
    }

    protected function getTable(): string
    {
        return $this->table;
    }

    protected function dbTableExists(string $table): bool
    {
        $stm = $this->getPdo()->prepare("SHOW TABLES LIKE :table");
        $stm->execute(compact('table'));
        return $stm->fetchColumn() !== false;
    }

    /**
     * Load this object with all available data from the DB
     */
    public function load(): static
    {
        try {
            if (!$this->dbTableExists($this->getTable())) return $this;

            $stm = $this->getPdo()->prepare("SELECT * FROM {$this->getTable()}");
            $stm->execute();

            foreach ($stm as $row) {
                $this->set($row->key, $this->encodeValue($row->value));
            }
        } catch (\Exception $e) { \Tk\Log::error($e->__toString());}
        return $this;
    }

    /**
     * Save modified Data to the DB
     */
    public function save(): static
    {
        try {
            foreach($this as $k => $v) {
                $this->dbSet($k, $v);
            }
            foreach ($this->del as $k => $v) {
                $this->dbDelete($k);
            }
        } catch (\Exception $e) { \Tk\Log::error($e->__toString());}
        return $this;
    }

    /**
     * Remove item from collection
     */
    public function remove(string $key): static
    {
        if ($this->has($key)) {
            $this->del[$key] = $this->get($key);
            parent::remove($key);
        }
        return $this;
    }

    /**
     * Remove all items from collection
     */
    public function clear(): Collection
    {
        foreach ($this as $k => $v) {
            $this->remove($k);
        }
        return $this;
    }

    /**
     * Check if a value exists in the DB
     */
    protected function dbHas(string $key): bool
    {
        if (!$this->dbTableExists($this->getTable())) return false;

        $stm = $this->getPdo()->prepare("SELECT * FROM {$this->getTable()} WHERE `key` = :key");
        $stm->execute(compact('key'));
        return $stm->rowCount() > 0;
    }

    protected function dbSet(string $key, $value): static
    {
        $this->installTable();
        $value = $this->encodeValue($value);

        if ($this->dbHas($key)) {
            $stm = $this->getPdo()->prepare("UPDATE {$this->getTable()} SET value = :value WHERE `key` = :key");
        } else {
            $stm = $this->getPdo()->prepare("INSERT INTO {$this->getTable()} (`key`, value) VALUES (:key, :value)");
        }
        $stm->execute(compact('key', 'value'));
        return $this;
    }

    protected function dbGet(string $key): mixed
    {
        if (!$this->dbTableExists($this->getTable())) return '';
        $stm = $this->getPdo()->prepare("SELECT value FROM {$this->getTable()} WHERE `key` = :key");
        $stm->execute(compact('key'));
        return $this->decodeValue($stm->fetchColumn() ?? '');
    }

    protected function dbDelete(string $key): static
    {
        if (!$this->dbTableExists($this->getTable())) return $this;
        $stm = $this->getPdo()->prepare("DELETE FROM {$this->getTable()} WHERE `key` = :key");
        $stm->execute(compact('key'));
        return $this;
    }

    /**
     * return true if the table was created
     */
    public function installTable(): bool
    {
        try {
            if ($this->dbTableExists($this->getTable())) return false;
            $this->getPdo()->exec($this->getTableSql());

        } catch (\Exception $e) { \Tk\Log::error($e->__toString());}
        return true;
    }

    public function getTableSql(): string
    {
        return <<<SQL
            CREATE TABLE IF NOT EXISTS {$this->getTable()} (
              `key` VARCHAR(128) NOT NULL PRIMARY KEY,
              `value` TEXT
            );
        SQL;
    }

    protected function decodeValue(string $value): mixed
    {
        if (preg_match('/^(___JSON:)/', $value)) {
            $value = json_decode(substr($value, 8));
        }
        return $value;
    }

    protected function encodeValue(mixed $value): string
    {
        if (is_array($value) || is_object($value)) {
            $value = '___JSON:' . json_encode($value);
        }
        return $value;
    }

}