<?php
namespace Tk\DataMap;


/**
 * This DataMap object is used to load objects and arrays
 * from a collection of DataTypes.
 *
 * Load it with the data types you want to map from an object to
 * an array and vica-versa and use loadObject() and loadArray()
 * to populate your objects.
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class DataMap
{

    /**
     * A list of types sorted by property name
     * @var DataTypeInterface[]|array
     */
    private array $propertyTypes = [];

    /**
     * A list of types sorted by key name
     * @var DataTypeInterface[]|array
     */
    private array $keyTypes = [];

    /**
     * If this is true objects without the defined property will be added dynamically
     */
    protected bool $enableDynamic = true;


    /**
     * Map all types from an array to an object.
     *
     * If the property does not exist in the object the type`s value is added to
     * the object as a dynamic property. If DataMap::dynamicProperties is set to true.
     *
     * @param array $ignorePropertyTypes An array of property names to ignore
     * @link http://krisjordan.com/dynamic-properties-in-php-with-stdclass
     */
    public function loadObject(object $object, array $srcArray, array $ignorePropertyTypes = []): DataMap
    {
        // We load from the source array here, then we can add dynamic property values
        foreach ($srcArray as $key => $value) {
            $type = $this->getKeyType($key);
            if ($type) {
                if (in_array($type->getProperty(), $ignorePropertyTypes)) continue;
                $type->loadObject($object, $srcArray);
            } else {
                if ($this->getPropertyType($key)) continue;
                try {
                    if ($this->isEnableDynamic()) {
                        $reflect = new \ReflectionClass($object);
                        if (!$reflect->hasProperty($key)) {
                            $object->$key = $value;
                        }
                    }
                } catch (\ReflectionException $e) { }
            }
        }
        return $this;
    }

    /**
     * Using the DataMap load an array with the values from an object
     *
     * @param array $ignoreKeyTypes An array of key names to ignore
     */
    public function loadArray(array &$array, object $srcObject, array $ignoreKeyTypes = []): DataMap
    {
        foreach ($this->getPropertyTypes() as $type) {
            if (in_array($type->getKey(), $ignoreKeyTypes)) continue;
            $type->loadArray($array, $srcObject);
        }
        return $this;
    }

    public function getArray(object $srcObject, array $ignoreKeyTypes = []): array
    {
        $array = [];
        $this->loadArray($array, $srcObject, $ignoreKeyTypes);
        return $array;
    }

    /**
     * Add a DataType to this data map
     *
     * @return DataTypeInterface|null
     */
    public function addDataType(DataTypeInterface $type): DataTypeInterface
    {
        $this->propertyTypes[$type->getProperty()] = $type;
        $this->keyTypes[$type->getKey()] = $type;
        return $type;
    }

    /**
     * Gets the list of property types.
     *
     * @return array|DataTypeInterface[]
     */
    public function getPropertyTypes(): array
    {
        return $this->propertyTypes;
    }

    /**
     * Gets a type by its property name
     */
    public function getPropertyType(string $property): ?DataTypeInterface
    {
        return $this->propertyTypes[$property] ?? null;
    }

    /**
     * Gets the list of key types.
     *
     * @return array|DataTypeInterface[]
     */
    public function getKeyTypes(): array
    {
        return $this->keyTypes;
    }

    /**
     * Gets a type by its key name
     */
    public function getKeyType(string $key): ?DataTypeInterface
    {
        return $this->keyTypes[$key] ?? null;
    }


    public function isEnableDynamic(): bool
    {
        return $this->enableDynamic;
    }

    public function setEnableDynamic(bool $enableDynamic): DataMap
    {
        $this->enableDynamic = $enableDynamic;
        return $this;
    }

}