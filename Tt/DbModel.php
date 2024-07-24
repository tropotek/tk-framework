<?php

namespace Tt;

use Tk\Factory;
use Tt\DbMap;

abstract class DbModel
{
    /**
     * @var DataMap[]|array
     */
    protected static array $_DATA_MAPS = [];

    protected string $_primaryKey = 'id';


    public static function autoDataMap(string $table, string $view = ''): DataMap
    {
        $map = new DataMap();

        // autogenerate a datamap from DB and object metadata

        return $map;
    }


    /**
     * Tk lib magic DB mapping method
     * Map table columns to object properties
     *
     * Override to map custom object properties
     */
    public function __map(array $row, array $dbMeta = []): void
    {
        $reflect = new \ReflectionClass($this);
        foreach ($row as $column => $colVal) {
            $meta = $dbMeta[$column] ?? null;
            if (!$meta) {
                $this->$column = $colVal;
                continue;
            }
            if (!$reflect->hasProperty($meta->name_camel)) continue;
            if ($meta->is_primary_key) $this->_primaryKey = $meta->name_camel;

            // map value to php data type
            $rProperty = $reflect->getProperty($meta->name_camel);
            $value = $colVal;

            // map custom objects
            if (!is_null($value)) {
                if ($meta->is_datetime) {
                    $tz = null;
                    if ($meta->timezone && $meta->timezone != 'SYSTEM') {
                        $tz = new \DateTimeZone($meta->timezone);
                    }
                    $value = new \DateTime($value, $tz);
                } else if ($meta->is_json) {
                    $value = json_decode($value);
                }
            }
            $rProperty->setValue($this, $value);
        }
    }

    /**
     * Tk lib magic DB unmapping method
     * Map object properties to a tables columns
     */
    public function __unmap(?array $props = null): array
    {
        $reflect = new \ReflectionClass($this);
        if (!$props) $props = array_map(fn($r) => $r->getName(), $reflect->getProperties());
        $values = [];
        foreach ($props as $prop => $filter) {
            $column = strtolower(preg_replace('/(?<!^)[A-Z]+|(?<!^|\d)[\d]+/', '_$0', $prop));
            $values[$column] = is_callable($filter) ? call_user_func($filter, $this->$prop) : $this->$prop;
        }
        return $values;
    }

    // ---- Base public methods ----

    // implement these in the model class
    abstract public static function get(int $id): ?static;
    abstract public static function getAll(): array;

//    public static function mustGet(int $id): static
//    {
//        $obj = static::get($id);
//		assert($obj instanceof self, "failed to get ".static::class." object id {$id}");
//		return $obj;
//    }


    // ---- Base public methods ----

	/**
	 * load properties of this object from database
	 * necessary to set properties if using views
	 */
    public function reload(): void
    {
        if ($this->getId()) {
            $obj = static::get($this->getId());
        } else {
            $obj = new static();
        }
		foreach (get_object_vars($obj) as $prop => $val) {
			$this->$prop = $val;
		}
    }

    public function getId(): int
    {
        if (!$this->_primaryKey) return 0;
        return intval($this->{$this->_primaryKey});
    }

    // todo mm this may not be the best place for this call ?
    public static function getDb(): Db
    {
        return Factory::instance()->getDbNew();
    }

}