<?php
namespace Tk\DataMap;

use Tk\Db\Model;

/**
 * A DataMap is a collection of DataTypeInterface objects used to map/unmap
 * an object to/from an array.
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
     * @var array<string, DataTypeInterface>
     */
    private array $propertyTypes = [];

    /**
     * A list of types indexed by column name
     * @var array<string, DataTypeInterface>
     */
    private array $columnTypes = [];

    /**
     * true if the object constructor has required parameters
     */
    private bool $constructorRequiresParams = false;


    /**
     * Use the DataMap to load an object with the values from an array
     *
     * If the property does not exist in the object the type`s value is added to
     * the object as a dynamic property. If DataMap::dynamicProperties is set to true.
     *
     * @param array<string, mixed> $srcArray
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
     * Use the DataMap to load an array with the values from an object
     *
     * @param array<string, mixed> $array
     */
    public function loadArray(array &$array, object $srcObject, int $access = self::WRITE): self
    {
        foreach ($this->propertyTypes as $type) {
            if (!$type->hasAccess($access)) continue;
            $type->loadArray($array, $srcObject);
        }
        return $this;
    }

    /**
     * @return array<string, mixed>
     */
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

    /**
     * @return list<string>
     */
    public function getColumnNames(): array
    {
        return array_keys($this->columnTypes);
    }

    /**
     * @return list<string>
     */
    public function getPropertyNames(): array
    {
        return array_keys($this->propertyTypes);
    }

    public function getConstructorRequiresParams(): bool
    {
        return $this->constructorRequiresParams;
    }

    public function setConstructorRequiresParams(bool $requiresParams): self
    {
        $this->constructorRequiresParams = $requiresParams;
        return $this;
    }

}