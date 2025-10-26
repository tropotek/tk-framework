<?php
namespace Tk\Db;

use Tk\DataMap\Db\DateTime;
use Tk\DataMap\ModelMapper;
use Tk\Money;
use Tk\ObjectUtil;
use Tk\Traits\DataTrait;
use Tk\DataMap\DataMap;
use Tk\Db;

/**
 * This class contains the default behavior for common operations such as
 * retrieving, saving, and mapping/unmapping objects for databases and forms.
 *
 * When implementing your own Models, constructors cannot have any required params.
 * <?php
 *      // OK
 *      public function __construct(int $testId = 0)
 *      {
 *          $this->testId = $testId;
 *      }
 *
 *      // ERROR
 *      public function __construct(int $testId)
 *      {
 *          $this->testId = $testId;
 *      }
 *
 *      // As an alternative use a factory method, to use any params on construction
 *      public static function create(int $testId): self
 *      {
 *          $obj = new self();
 *          $obj->testId = $testId;
 *          return $obj;
 *      }
 * ?>
 *
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
        $table = ModelMapper::instance()->getPrimaryTable(static::class);
        $column = self::getPrimaryColumn();
        $obj = Db::queryOne("
            SELECT *
            FROM {$table}
            WHERE {$column} = :id", compact('id'), static::class);
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
        Db::$CACHE_LAST = false;
        $map = self::getDataMap();
        $priKey = $map->getPrimaryKey()?->getProperty();
        if (is_null($priKey)) {
            Db::$CACHE_LAST = true;
            return;
        }
        $id = $this->$priKey;

        if ($id) {
            $obj = static::find($id);
        } else {
            // Warning, if a model class has constructor params, this will fail.
            // models should use a factory method instead, eg: Object::create(...$params).
            // @phpstan-ignore-next-line
            $obj = new static();
        }
        if (is_null($obj)) {
            Db::$CACHE_LAST = true;
            return;
        }
        foreach (get_object_vars($obj) as $prop => $val) {
            $this->$prop = $val;
        }
        Db::$CACHE_LAST = true;
    }

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


    public static function getDataMap(): DataMap
    {
        return ModelMapper::instance()->getDataMap(static::class);
    }

    /**
     * Used by the \Tk\Db and \Tk\Db\Statement objects to map data to an object
     */
    public function __map(array $row, ?DataMap $map = null): void
    {
        Db::$CACHE_LAST = false;
        if (is_null($map)) {
            $map = self::getDataMap();
        }
        $map->loadObject($this, $row);
        Db::$CACHE_LAST = true;
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
     * Get the primary key object property name
     */
    public static function getPrimaryProperty(): string
    {
        return self::getDataMap()->getPrimaryKey()?->getProperty() ?? '';
    }

    /**
     * Get the primary key DB column name
     */
    public static function getPrimaryColumn(): string
    {
        return self::getDataMap()->getPrimaryKey()?->getColumn() ?? '';
    }

    public static function getPrimaryTable(): string
    {
        return ModelMapper::instance()->getPrimaryTable(static::class);
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
}