<?php
namespace Tk\DataMap;

use Tk\ObjectUtil;

abstract class DataTypeInterface
{

    /**
     * The object property name
     */
    protected string $property = '';

    /**
     * The storage column name, (IE: db column, form field, array key)
     */
    protected string $column = '';

    /**
     * Store any flags/attributes related to this data type
     */
    protected array $flags = [];

    /**
     * Data type IO access
     */
    protected int $access = 0;


    /**
     * @param string $property The object property to map the column to.
     * @param string $column (optional) The column name to map this property to. (default: $property)
     */
    public function __construct(string $property, string $column = '')
    {
        $this->access = DataMap::READ | DataMap::WRITE;
        $this->property = $property;
        $this->column = $column ?: $property;
    }

    public function setAccess(int $access): self
    {
        $this->access = $access;
        return $this;
    }

    public function hasAccess(int $access): bool
    {
        return ($access & $this->access) != 0;
    }

    public function isRead(): bool
    {
        return $this->hasAccess(DataMap::READ);
    }

    public function isWrite(): bool
    {
        return $this->hasAccess(DataMap::WRITE);
    }

    /**
     * Get the storage column value from an object property
     * Returns null if property not found or value is null
     * This should generally return a native PHP type (Default: null|string).
     */
    public function getColumnValue(object $object): mixed
    {
        $v = null;
        if ($this->hasProperty($object)) {
            $v = ObjectUtil::getPropertyValue($object, $this->getProperty());
        }
        return $v;
    }

    /**
     * Return an object property value from an array column
     * This should return the column value converted to the object property type
     */
    public function getPropertyValue(array $array): mixed
    {
        if (array_key_exists($this->getColumn(), $array)) {
            return $array[$this->getColumn()];
        }
        return null;
    }

    /**
     * Set the objects property from the supplied array values
     */
    public function loadObject(object $object, array $srcArray): DataTypeInterface
    {
        $value = $this->getPropertyValue($srcArray);
        ObjectUtil::setPropertyValue($object, $this->getProperty(), $value);
        return $this;
    }

    /**
     * Set the array key/value from the object`s properties
     */
    public function loadArray(array &$array, object $srcObject): DataTypeInterface
    {
        $value = $this->getColumnValue($srcObject);
        $array[$this->getColumn()] = $value;
        return $this;
    }

    /**
     * The object property name
     */
    public function getProperty(): string
    {
        return $this->property;
    }

    /**
     * return the storage column name
     */
    public function getColumn(): string
    {
        return $this->column;
    }

    public function hasProperty(object $object): bool
    {
        return ObjectUtil::objectPropertyExists($object, $this->getProperty());
    }

    public function hasColumn(array $array): bool
    {
        return array_key_exists($this->getColumn(), $array);
    }

    public function getFlags(): array
    {
        return $this->flags;
    }

    public function hasFlag(string $name): bool
    {
        return in_array($name, $this->flags);
    }

    public function setFlag(string $name): DataTypeInterface
    {
        $this->flags[] = $name;
        return $this;
    }

}