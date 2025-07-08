<?php
namespace Tk\DataMap;

use Tk\Db\Model;

/**
 * This DataMap object is used to load objects and arrays
 * from a collection of DataTypes.
 *
 * Load it with the data types you want to map from an object to
 * an array and vica-versa and use loadObject() and loadArray()
 * to populate your objects.
 */
class DataMap
{
    const string PRI    = 'pri';    // DB primary key flag

    // Data type IO flags
    // READ => property will be read into the object
    // WRITE => object property will be written to teh storage array
    // READ|WRITE => (default) property will be read into the object and written to the storage array
    const int READ       = 0x01;      // For view/table reads only
    const int WRITE      = 0x02;      // For table writes only

    /**
     * A list of types indexed by property name
     * @var DataTypeInterface[]|array
     */
    private array $propertyTypes = [];

    /**
     * A list of types indexed by column name
     * @var DataTypeInterface[]|array
     */
    private array $columnTypes = [];


    /**
     * Map all types from an array to an object.
     *
     * If the property does not exist in the object the type`s value is added to
     * the object as a dynamic property. If DataMap::dynamicProperties is set to true.
     */
    public function loadObject(object $object, array $srcArray, int $access = self::READ): self
    {
        foreach ($srcArray as $key => $value) {
            $type = $this->getTypeByColumn($key);
            if (!($type instanceof DataTypeInterface)) continue;

            if ($type->hasAccess($access)) {
                $type->loadObject($object, $srcArray);
            } else {
                if ($object instanceof Model) {
                    if (property_exists($object, $key)) continue;
                    $object->$key = $value;
                }
            }
        }
        return $this;
    }

    /**
     * Using the DataMap load an array with the values from an object
     */
    public function loadArray(array &$array, object $srcObject, int $access = self::WRITE): self
    {
        foreach ($this->propertyTypes as $type) {
            if (!$type->hasAccess($access)) continue;
            $type->loadArray($array, $srcObject);
        }
        return $this;
    }

    public function getArray(object $srcObject, int $access = self::WRITE): array
    {
        $array = [];
        $this->loadArray($array, $srcObject, $access);
        return $array;
    }

    /**
     * Add a mapper data type to this data map
     */
    public function addType(DataTypeInterface $type, int $access = 0): DataTypeInterface
    {
        if ($access) {
            $type->setAccess($access);
        }

        $this->propertyTypes[$type->getProperty()] = $type;
        $this->columnTypes[$type->getColumn()] = $type;
        return $type;
    }

    public function getTypeByProperty(string $property): ?DataTypeInterface
    {
        return $this->propertyTypes[$property] ?? null;
    }

    public function getTypeByColumn(string $column): ?DataTypeInterface
    {
        return $this->columnTypes[$column] ?? null;
    }

    public function getPrimaryKey(): ?DataTypeInterface
    {
        foreach ($this->propertyTypes as $type) {
            if ($type->hasFlag(DataMap::PRI)) return $type;
        }
        return null;
    }

    public function getColumnNames(): array
    {
        return array_keys($this->columnTypes);
    }

    public function getPropertyNames(): array
    {
        return array_keys($this->propertyTypes);
    }

    public static function makeDbType(string $phpType, string $propertyName, string $columnName): DataTypeInterface
    {
        return match ($phpType) {
            'bool'     => new Db\Boolean($propertyName, $columnName),
            'int'      => new Db\Integer($propertyName, $columnName),
            'float'    => new Db\Decimal($propertyName, $columnName),
            'array'    => new Db\ArrayType($propertyName, $columnName),
            'json'     => new Db\Json($propertyName, $columnName),
            'timestamp', 'datetime' => new Db\DateTime($propertyName, $columnName),
            'date'     => new Db\Date($propertyName, $columnName),
            'time'     => new Db\Time($propertyName, $columnName),
            'year'     => new Db\Year($propertyName, $columnName),
            'Tk\Money' => new Db\Money($propertyName, $columnName),
            default    => new Db\Text($propertyName, $columnName),
        };
    }

    public static function makeFormType(string $type, string $property): DataTypeInterface
    {
        return match ($type) {
            'bool'     => new Form\Boolean($property),
            'int'      => new Form\Integer($property),
            'float'    => new Form\Decimal($property),
            'array'    => new Form\ArrayType($property),
            'percent'  => new Form\Percent($property),
            'Tk\Money' => new Form\Money($property),
            'DateTime' => new Form\Date($property),
            default    => new Form\Text($property),
        };
    }
}