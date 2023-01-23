<?php
namespace Tk\Db;

use Tk\Traits\ConfigTrait;
use Tk\Traits\FactoryTrait;
use Tk\Traits\SystemTrait;

/**
 * A collection object that can store its values in a DB table.
 *
 * The main difference to the Collection object is that you will need
 * to supply a table name and PDO object to create an instance.
 *
 * After you have made your modifications you must call the Collection->save();
 * method so the data is saved from memory to the DB table.
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class Collection extends \Tk\Collection
{
    use SystemTrait;


    protected string $table = '';

    private array $del = [];

    private Pdo $db;


    public function __construct(string $table)
    {
        parent::__construct();
        $this->table = $table;
    }


    public function __sleep()
    {
        return ['table', 'del'];
    }

    public function __wakeup()
    {
        $this->setDb($this->getFactory()->getDb());
    }

    /**
     * Creates an instance of the Data object and loads that data from the DB
     * By Default this method uses the Config::getDb() to get the database.
     */
    public static function create(string $table): static
    {
        $obj = new static($table);
        $obj->setDb($obj->getFactory()->getDb());
        $obj->load();
        return $obj;
    }

    public function getDb(): Pdo
    {
        return $this->db;
    }

    public function setDb($db): static
    {
        $this->db = $db;
        return $this;
    }

    /**
     * Get the table name for queries
     */
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
            if (!$this->getDb()->hasTable($this->getTable())) return $this;

            $sql = sprintf('SELECT * FROM %s WHERE 1',
                $this->getDb()->quoteParameter($this->getTable()));

            $stmt = $this->getDb()->query($sql);
            $stmt->setFetchMode(\PDO::FETCH_OBJ);
            foreach ($stmt as $row) {
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
     * Set a single data value in the Database
     */
    protected function dbSet(string $key, $value): static
    {
        $this->installTable();
        $value = $this->encodeValue($value);

        if ($this->dbHas($key)) {
            $sql = sprintf('UPDATE %s SET value = %s WHERE %s = %s',
                $this->getDb()->quoteParameter($this->getTable()),
                $this->getDb()->quote($value), $this->getDb()->quoteParameter('key'),
                $this->getDb()->quote($key));
        } else {
            $sql = sprintf('INSERT INTO %s (%s, value) VALUES (%s, %s)',
                $this->getDb()->quoteParameter($this->getTable()),
                $this->getDb()->quoteParameter('key'),
                $this->getDb()->quote($key), $this->db->quote($value));
        }
        $this->getDb()->exec($sql);
        return $this;
    }

    /**
     * Get a value from the database
     *
     * @return string|mixed
     * @throws Exception
     */
    protected function dbGet(string $key)
    {
        if (!$this->getDb()->hasTable($this->getTable())) return '';
        $sql = sprintf('SELECT * FROM %s WHERE %s = %s',
            $this->getDb()->quoteParameter($this->getTable()),
            $this->getDb()->quoteParameter('key'),
            $this->getDb()->quote($key)
        );

        $row = $this->getDb()->query($sql)->fetchObject();
        if ($row) {
            return $this->decodeValue($row->value);
        }
        return '';
    }

    /**
     * Check if a value exists in the DB
     * @throws Exception
     */
    protected function dbHas(string $key): bool
    {
        if (!$this->getDb()->hasTable($this->getTable())) return false;
        $sql = sprintf('SELECT * FROM %s WHERE %s = %s',
            $this->getDb()->quoteParameter($this->getTable()),
            $this->getDb()->quoteParameter('key'),
            $this->getDb()->quote($key)
        );

        $res = $this->getDb()->query($sql);
        if ($res && $res->rowCount()) return true;
        return false;
    }

    /**
     * Remove a value from the DB
     * @throws Exception
     */
    protected function dbDelete(string $key): static
    {
        if (!$this->getDb()->hasTable($this->getTable())) return $this;
        $sql = sprintf('DELETE FROM %s WHERE %s = %s',
            $this->getDb()->quoteParameter($this->getTable()),
            $this->getDb()->quoteParameter('key'),
            $this->getDb()->quote($key));

        $this->getDb()->exec($sql);
        return $this;
    }

    /**
     * This sql should be DB generic (tested on: mysql, pgsql)
     *
     * @return bool Return true if the table was created
     */
    public function installTable(): bool
    {
        try {
            if ($this->getDb()->hasTable($this->getTable())) return false;
            $this->getDb()->exec($this->getTableSql($this->getDb()->getDriver()));

        } catch (\Exception $e) { \Tk\Log::error($e->__toString());}
        return true;
    }

    public function getTableSql(string $type = 'mysql'): string
    {
        $tbl = $this->getDb()->quoteParameter($this->getTable());
        return match ($type) {
            'mysql' => <<<SQL
    CREATE TABLE IF NOT EXISTS $tbl (
      `key` VARCHAR(128) NOT NULL PRIMARY KEY,
      `value` TEXT
    ) ENGINE=InnoDB;
SQL,
            default => <<<SQL
    CREATE TABLE IF NOT EXISTS $tbl (
      "key" VARCHAR(128) NOT NULL PRIMARY KEY,
      "value" TEXT
    );
SQL,
        };
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