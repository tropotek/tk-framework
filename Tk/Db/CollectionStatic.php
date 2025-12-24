<?php
namespace Tk\Db;

use Tk\Db;

/**
 * A collection object that can store its values in a DB table.
 * StaticCollection objects are serialized and stored in a single row with the supplied key.
 * Data changes are immediately saved to the DB.
 *
 * The main difference to the Collection object is that you will need
 * to supply a table name and PDO object to create an instance.
 */
class CollectionStatic extends \Tk\Collection
{

    protected string $table = '';
    protected string $keyId = '';


    public function __construct(string $table, string $keyId)
    {
        parent::__construct();
        $this->table = $table;
        $this->keyId = $keyId;
        $this->dbLoad();
    }

    static function create(string $table, string $keyId): static
    {
        return new static($table, $keyId);
    }

    public function __sleep()
    {
        return ['table', 'keyId', '_data'];
    }

    /**
     * Add a list of items to the collection
     */
    public function replace(array $items): static
    {
        parent::replace($items);
        $this->dbSave();
        return $this;
    }

    public function set(string $key, mixed $value): static
    {
        parent::set($key, $value);
        $this->dbSave();
        return $this;
    }

    public function prepend(string $key, mixed $value, ?string $before = null): mixed
    {
        $obj = parent::prepend($key, $value, $before);
        $this->dbSave();
        return $obj;
    }

    public function append(string $key, mixed $value, ?string $after = null): mixed
    {
        $obj = parent::append($key, $value, $after);
        $this->dbSave();
        return $obj;
    }

    public function remove(string $key): static
    {
        parent::remove($key);
        $this->dbSave();
        return $this;
    }

    /**
     * Remove all items from a collection
     */
    public function clear(): static
    {
        $obj = parent::clear();
        $this->dbSave();
        return $obj;
    }

    /**
     * return true if the collection row exists in the DB
     */
    protected function dbExists(): bool
    {
        return Db::queryBool("
            SELECT EXISTS(
                SELECT * FROM {$this->getTable()} WHERE key_id = :key_id
            )",
            [
                'key_id' => $this->keyId
            ]
        );
    }

    /**
     * Load this object with all available data from the DB
     */
    protected function dbLoad(): static
    {
        try {
            if (!$this->installTable()) return $this;
            $dataStr = Db::queryString("
                SELECT value
                FROM {$this->getTable()}
                WHERE key_id = :key_id",
                ['key_id' => $this->keyId]
            );
            $arr = unserialize($dataStr);
            if (is_array($arr)) {
                $this->_data = $arr;
            }
        } catch (\Exception $e) { \Tk\Log::notice($e->__toString());}
        return $this;
    }

    /**
     * Save modified Data to the DB
     */
    protected function dbSave(): static
    {
        try {
            Db::insertUpdate($this->getTable(), 'key_id', ['key_id' => $this->keyId, 'value' => serialize($this->_data)]);
        } catch (\Exception $e) { \Tk\Log::notice($e->__toString());}
        return $this;
    }

    /**
     * return true if the table exists or is installed
     */
    protected function installTable(): bool
    {
        if (Db::tableExists($this->getTable())) return true;
        return false !== Db::execute($this->getTableSql());
    }

    protected function getTableSql(): string
    {
        return <<<SQL
            CREATE TABLE IF NOT EXISTS {$this->getTable()} (
              key_id VARCHAR(128) NOT NULL PRIMARY KEY,
              value TEXT
            );
        SQL;
    }

    protected function getTable(): string
    {
        return $this->table;
    }
}