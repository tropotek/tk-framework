<?php
namespace Tk\DataMap;

use Tk\Db\Model;
use Tk\ObjectUtil;
use Tk\Traits\DataTrait;

/**
 * The ModelMapper is a Factory object to get/create Model class data maps.
 *
 * New Data types can be added using `ModelMapper::instance()->addMapType(...)` in your app bootstrap code.
 *
 * This object can be extended if you want to add more data maps types for other storage options other than DB and forms.
 */
class ModelMapper
{
    use DataTrait;

    private static mixed $_instance = null;

    const array FORCE_READ_ONLY = ['modified', 'created'];

    const string MAP_DB   = 'db';
    const string MAP_FORM = 'form';

    /**
     * A cache to hold created class datamaps using the model class name as the key
     * @var array<string, DataMap>
     */
    protected array $classMaps = [];

    /**
     * A collection of available DataTypeInterface objects used to map database columns to object properties.
     * Adds DataTypeInterface classes to these arrays to add new data types.
     *
     * ModelMapper::instance()->addMapType(ModelMapper::MAP_DB, 'bool', \App\DataMap\Db\Boolean::class);
     *
     * @var array<string, array<string, string>>
     */
    protected array $dataTypes = [];


    protected function __construct()
    {
        $this->initDefaultDbTypes();
        $this->initDefaultFormTypes();
    }

    public static function instance(): self
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * init default DB data types
     */
    public function initDefaultDbTypes(): void
    {
        $this->addMapType(self::MAP_DB, '_default', Db\Text::class);

        $this->addMapType(self::MAP_DB, 'bool', Db\Boolean::class);
        $this->addMapType(self::MAP_DB, 'int', Db\Integer::class);
        $this->addMapType(self::MAP_DB, 'float', Db\Decimal::class);
        $this->addMapType(self::MAP_DB, 'array', Db\ArrayType::class);
        $this->addMapType(self::MAP_DB, 'json', Db\Json::class);
        $this->addMapType(self::MAP_DB, 'timestamp', Db\DateTime::class);
        $this->addMapType(self::MAP_DB, 'datetime', Db\DateTime::class);
        $this->addMapType(self::MAP_DB, 'date', Db\Date::class);
        $this->addMapType(self::MAP_DB, 'time', Db\Time::class);
        $this->addMapType(self::MAP_DB, 'year', Db\Year::class);
        $this->addMapType(self::MAP_DB, 'Tk\Money', Db\Money::class);
    }

    /**
     * init default Form data types
     */
    public function initDefaultFormTypes(): void
    {
        $this->addMapType(self::MAP_FORM, '_default', Form\Text::class);

        $this->addMapType(self::MAP_FORM, 'bool', Form\Boolean::class);
        $this->addMapType(self::MAP_FORM, 'int', Form\Integer::class);
        $this->addMapType(self::MAP_FORM, 'float', Form\Decimal::class);
        $this->addMapType(self::MAP_FORM, 'array', Form\ArrayType::class);
        $this->addMapType(self::MAP_FORM, 'percent', Form\Percent::class);
        $this->addMapType(self::MAP_FORM, 'Tk\Money', Form\Money::class);
        $this->addMapType(self::MAP_FORM, 'DateTime', Form\Date::class);
    }

    public function addMapType(string $mapName, string $dataType, string $dataTypeClass): self
    {
        $this->dataTypes[$mapName][$dataType] = $dataTypeClass;
        return $this;
    }

    public function getMapType(string $mapName, string $dataType): ?string
    {
        return $this->dataTypes[$mapName][$dataType] ?? null;
    }

    public function hasMapType(string $mapName, string $dataType): bool
    {
        return array_key_exists($dataType, $this->dataTypes[$mapName]);
    }


    protected function getMap(string $name): ?DataMap
    {
        return $this->classMaps[$name] ?? null;
    }

    protected function setMap(string $name, DataMap $map): self
    {
        if (empty($name)) throw new \Exception('Map name cannot be empty');
        $this->classMaps[$name] = $map;
        return $this;
    }

    public function setDataMap(string $class, DataMap $map): self
    {
        return $this->setMap($this->getDataKey($class), $map);
    }

    public function setFormMap(string $class, DataMap $map): self
    {
        return $this->setMap($this->getFormKey($class), $map);
    }

    protected function hasMap(string $key): bool
    {
        return array_key_exists($key, $this->classMaps);
    }

    public function hasDataMap(string $class): bool
    {
        return array_key_exists($this->getDataKey($class), $this->classMaps);
    }

    public function hasFormMap(string $class): bool
    {
        return array_key_exists($this->getFormKey($class), $this->classMaps);
    }

    /**
     * Get the primary key object property name
     */
    public function getPrimaryProperty(string $class): string
    {
        return $this->getDataMap($class)->getPrimaryKey()?->getProperty() ?? '';
    }

    /**
     * Get the primary key DB column name
     */
    public function getPrimaryColumn(string $class): string
    {
        return $this->getDataMap($class)->getPrimaryKey()?->getColumn() ?? '';
    }

    /**
     * return a table or view that is used as the primary lookup table for the Model
     * Define the constant DB_TABLE in your Model object to force a specific table name
     */
    public function getPrimaryTable(string $class): string
    {
        if (!empty($class::DB_TABLE)) return $class::DB_TABLE;
        $view = static::getDbView($class);
        if ($view) return $view;
        return static::getDbTable($class);
    }

    /**
     * return a snake style DB table name generated from the class name
     */
    public function getDbTable(string $class): string
    {
        if (!empty($class::DB_TABLE)) return $class::DB_TABLE;
        $basename = strval(ObjectUtil::basename($class));
        return strtolower(preg_replace('/(?<!^)[A-Z]+|(?<!^|\d)[\d]+/', '_$0', $basename));
    }

    /**
     * return the DB view name if exists returns empty string if not
     * All views are assumed to be the default table name with 'v_' prepended
     */
    public function getDbView(string $class): string
    {
        $table = $this->getDbTable($class);
        $view = "v_{$table}";
        if (\Tk\Db::tableExists($view)) return $view;
        return '';
    }

    /**
     * Create a new DataTypeInterface object
     */
    public function makeType(string $mapName, string $dataType, string $propertyName, string $columnName = ''): DataTypeInterface
    {
        $typeClass = $this->getMapType($mapName, $dataType) ?? $this->getMapType($mapName, '_default');
        return new $typeClass($propertyName, $columnName);
    }

    public function getDataKey(string $class): string
    {
        return self::MAP_DB . '_' . $class;
    }

    public function getFormKey(string $class): string
    {
        return self::MAP_FORM . '_' . $class;
    }

    /**
     * Get a DB DataMap for a class
     * Creates and caches a new DataMap if it does not exist
     * Override this method if you need to create a custom DataMap with different table names
     */
    public function getDataMap(string $class): ?DataMap
    {
        if ($this->hasDataMap($class)) {
            return $this->getMap($this->getDataKey($class));
        }

        if (!is_subclass_of($class, Model::class)) return null;

        $cache = \Tk\Db::$CACHE_LAST;
        \Tk\Db::$CACHE_LAST = false;

        // read only table metadata
        $rMetaData = \Tk\Db::getTableInfo($this->getPrimaryTable($class), true);
        // writable table meta data
        $wMetaData = \Tk\Db::getTableInfo($this->getDbTable($class), true);
        $metaData = $wMetaData + $rMetaData;

        \Tk\Db::$CACHE_LAST = $cache;

        $rClass = new \ReflectionClass($class);
        $map = new DataMap();
        $map->setConstructorRequiresParams(ObjectUtil::constructorRequiresParams($class));

        $properties = $rClass->getProperties();
        foreach ($properties as $prop) {
            if (!array_key_exists($prop->getName(), $metaData)) continue;
            if (!($prop->getType() instanceof \ReflectionNamedType)) continue;

            $meta = $metaData[$prop->getName()];
            $phpType = $prop->getType()->getName();
            $dataType = $meta->php_type;

            if ($this->hasMapType(self::MAP_DB, $phpType)) {
                $dataType = $phpType;
            }
            // store default stdClass objects as a JSON string in the DB
            if ($phpType == 'stdClass' || str_starts_with($meta->name, 'json_')) {
                $dataType = 'json';
            }
            if ($phpType == 'array') {
                $dataType = 'array';
            }

            $type = $this->makeType(self::MAP_DB, $dataType, $prop->getName(), $meta->name);
            $type->setNullable($prop->getType()->allowsNull());
            if ($meta->is_primary_key) {
                $type->setFlag(DataMap::PRI);
            }

            // set property to read only if...
            if (
                $meta->Extra == 'VIRTUAL GENERATED' ||                      // is virtual field
                $prop->isReadOnly() ||                                      // prop is readonly
                !array_key_exists($prop->getName(), $wMetaData) ||          // col not in write table
                in_array($prop->getName(), self::FORCE_READ_ONLY)  // is framework readonly prop
            ) {
                $type->setAccess(DataMap::READ);
            }

            // set data type immutable sate
            if ($type instanceof Db\DateTime && $phpType == 'DateTimeImmutable') {
                $type->setImmutable(true);
            }

            $map->addType($type);
        }

        $this->setDataMap($class, $map);
        return $map;
    }

    /**
     * Get a Form DataMap for a class
     * Creates and caches a new DataMap if it does not exist
     * Override this method if you need to create a custom DataMap with different table names
     */
    public function getFormMap(string $class): ?DataMap
    {
        if ($this->hasFormMap($class)) {
            return $this->getMap($this->getFormKey($class));
        }

        if (!is_subclass_of($class, Model::class)) return null;

        $map = new DataMap();
        $primaryId = $this->getDataMap($class)->getPrimaryKey()->getProperty();

        $reflect = new \ReflectionClass($class);
        $props = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);
        foreach ($props as $prop) {
            if (str_starts_with($prop->getName(), '_') || $prop->isStatic()) continue;
            if (!($prop->getType() instanceof \ReflectionNamedType)) continue;

            $dataType = $prop->getType()->getName();

            // ignored types
            if (in_array($dataType, ['stdClass'])) continue;

            $type = $this->makeType(self::MAP_FORM, $dataType, $prop->getName());
            $type->setNullable($prop->getType()->allowsNull());

            if ($primaryId == $prop->getName()) {
                $type->setFlag(DataMap::PRI);
            }

            // set property to read only if...
            if (
                $prop->isReadOnly() ||
                in_array($prop->getName(), self::FORCE_READ_ONLY)
            ) {
                $type->setAccess(DataMap::READ);
            }

            $map->addType($type);
        }

        static::setMap($this->getFormKey($class), $map);

        return $map;
    }

}