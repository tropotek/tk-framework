<?php
namespace Tk\Db;

use Tk\Db;

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

    protected string $table = '';

    private array $del = [];


    public function __construct(string $table)
    {
        parent::__construct();
        $this->table = $table;
        $this->installTable();
    }

    public function __sleep()
    {
        return ['table', 'del'];
    }

    protected function getTable(): string
    {
        return $this->table;
    }

    /**
     * Load this object with all available data from the DB
     */
    public function load(): static
    {
        try {
            if (!$this->installTable()) return $this;
            $rows = Db::query("SELECT * FROM {$this->getTable()}");
            foreach ($rows as $row) {
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

    protected function dbHas(string $key): bool
    {
        if (!$this->installTable()) return false;
        $rows = Db::query("SELECT * FROM {$this->getTable()} WHERE `key` = :key", compact('key'));
        return count($rows) > 0;
    }

    protected function dbSet(string $key, $value): static
    {
        if (!$this->installTable()) return $this;
        $value = $this->encodeValue($value);
        if ($this->dbHas($key)) {
            Db::update($this->getTable(), 'key', compact('key', 'value'));
        } else {
            Db::insert($this->getTable(), compact('key', 'value'));
        }
        return $this;
    }

    protected function dbGet(string $key): mixed
    {
        if (!$this->installTable()) return '';
        $val = Db::queryVal("SELECT value FROM {$this->getTable()} WHERE `key` = :key",
            compact('key')
        );
        return $this->decodeValue($val ?? '');
    }

    protected function dbDelete(string $key): static
    {
        if (!$this->installTable()) return $this;
        Db::delete($this->getTable(), compact('key'));
        return $this;
    }

    /**
     * return true if the table exists or is installed
     */
    public function installTable(): bool
    {
        if (Db::tableExists($this->getTable())) return true;
        return false !== Db::execute($this->getTableSql());
    }

    protected function getTableSql(): string
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