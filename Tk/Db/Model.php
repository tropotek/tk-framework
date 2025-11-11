<?php
namespace Tk\Db;

use Tk\DataMap\ModelMapper;
use Tk\ObjectUtil;
use Tk\Traits\DataTrait;
use Tk\DataMap\DataMap;
use Tk\Db;

/**
 * A Base DB/Form Model class, it contains the default data map behavior for common DB and Form operations.
 *
 * Note: If your object constructor has required params, the object will be created using reflection, and
 *       your object may not be initialized correctly when you set default values within the constructor.
 *       It is recommended to use a factory method such as `MyObject::create($params)` for required constructor params,
 *       keeping the default initialization code within the constructor when object instantiation requires params.
 */
abstract class Model
{
    use DataTrait;

    /**
     * Leave this empty to use the default table/view
     * Default table/view name is the snake case value of the class name
     * @see static::getPrimaryTable()
     */
    const string DB_TABLE = '';

    /**
     * Return the primary int id of a model
     * Does not support models with a non-integer ID type
     */
    public function getId(): int
    {
        $priKey = static::getPrimaryProperty();
        if (empty($priKey) || !is_numeric($this->$priKey)) return 0;
        return intval($this->$priKey);
    }


    public static function find(int $id): ?static
    {
        $table = ModelMapper::instance()->getPrimaryTable(static::class);
        $column = self::getPrimaryColumn();
        return Db::queryOne("
            SELECT *
            FROM {$table}
            WHERE {$column} = :id", compact('id'), static::class);
    }

    /**
     * throws an exception on failure to find row
     */
    public static function mustFind(int $id): static
    {
        $obj = self::find($id);
        if (!($obj instanceof static)) {
            throw new \Tk\Exception(static::class . " not found with id {$id}");
        }
        return $obj;
    }

    /**
     * @return array<int,static>
     */
    public static function findAll(): array
    {
        $table = ModelMapper::instance()->getPrimaryTable(static::class);
        return Db::query("
            SELECT *
            FROM {$table}", [], static::class);
    }

    /**
     * reload object properties from the database
     */
    public function reload(): void
    {
        $map = self::getDataMap();
        $priKey = $map->getPrimaryKey()?->getProperty();
        if (is_null($priKey)) {
            return;
        }
        $id = $this->$priKey;

        if ($id) {
            $obj = static::find($id);
        } else {
            // if a constructor requires params, the object will be created without executing the constructor
            $obj = ObjectUtil::createObjectInstance(static::class, $map->getConstructorRequiresParams());
        }
        if (is_null($obj)) {
            return;
        }
        foreach (get_object_vars($obj) as $prop => $val) {
            $this->$prop = $val;
        }
    }

    public static function getDataMap(): DataMap
    {
        return ModelMapper::instance()->getDataMap(static::class);
    }

    /**
     * Used by the \Tk\Db and \Tk\Db\Statement objects to map data to an object
     */
    public function __map(array $row, ?DataMap $map = null): void
    {
        if (is_null($map)) {
            $map = self::getDataMap();
        }
        $map->loadObject($this, $row);
    }

    public static function getFormMap(): DataMap
    {
        return ModelMapper::instance()->getFormMap(static::class);
    }

    /**
     * map form values array to object properties
     */
    public function mapForm(array $values): static
    {
        $map = self::getFormMap();
        $map->loadObject($this, $values);
        return $this;
    }

    /**
     * map object properties to form values array
     */
    public function unmapForm(): array
    {
        $map = self::getFormMap();
        $values = [];
        $map->loadArray($values, $this);
        return $values;
    }

    /**
     * return a file data path for this object
     */
    public function getDataPath(): string
    {
        if (property_exists($this, 'dataPath')) {
            return $this->dataPath;
        }
        return '';
    }

    // ModelMapper helper methods

    public static function getPrimaryProperty(): string
    {
        return self::getDataMap()->getPrimaryKey()?->getProperty() ?? '';
    }

    public static function getPrimaryColumn(): string
    {
        return self::getDataMap()->getPrimaryKey()?->getColumn() ?? '';
    }

    public static function getPrimaryTable(): string
    {
        return ModelMapper::instance()->getPrimaryTable(static::class);
    }

    public static function getDbTable(): string
    {
        return ModelMapper::instance()->getDbTable(static::class);
    }
}