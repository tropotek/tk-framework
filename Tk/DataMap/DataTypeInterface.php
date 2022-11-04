<?php
namespace Tk\DataMap;


use Tk\ObjectUtil;

/**
 * @author Tropotek <http://www.tropotek.com/>
 */
abstract class DataTypeInterface
{

    /**
     * The storage key name, (IE: db column, form field, array key)
     */
    protected string $key = '';

    /**
     * The object property name
     */
    protected string $property = '';

    /**
     * Store any attributes related to this data type for the mapper
     */
    protected array $attributes = [];


    /**
     * @param string $property The object property to map the column to.
     * @param string $key (optional) The key name to map this property to. (default: $property)
     */
    public function __construct(string $property, string $key = '')
    {
        $this->property = $property;
        $this->key = $key ?: $property;
    }

    /**
     * Map an object property value to an array column value
     * Returns null if property not found or value is null
     * This should generally return a native PHP type or a string for forms and tables.
     */
    public function getPropertyValue(object $object): mixed
    {
        $v = null;
        if ($this->hasProperty($object)) {
            $v = ObjectUtil::getObjectPropertyValue($object, $this->getProperty());
        }
        return $v;
    }

    /**
     * Return the key value from the array supplied
     */
    public function getKeyValue(array $array): mixed
    {
        if (array_key_exists($this->getKey(), $array)) {
            return $array[$this->getKey()];
        }
        return null;
    }

    /**
     * Set the objects property from the supplied array values
     */
    public function loadObject(object $object, array $srcArray): DataTypeInterface
    {
        $value = $this->getKeyValue($srcArray);
        ObjectUtil::setPropertyValue($object, $this->getProperty(), $value);
        return $this;
    }

    /**
     * Set the array key/value from the object`s property
     */
    public function loadArray(array &$array, object $srcObject): DataTypeInterface
    {
        $value = $this->getPropertyValue($srcObject);
        $array[$this->getKey()] = $value;
        return $this;
    }

    /**
     * The object's instance property name
     */
    public function getProperty(): string
    {
        return $this->property;
    }

    /**
     * return the mapped array key name
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * return true if the column exists in the array
     */
    public function hasProperty(object $object): bool
    {
        return ObjectUtil::objectPropertyExists($object, $this->getProperty());
    }

    /**
     * return true if the array key exists in the src array
     */
    public function hasKey(array $array): bool
    {
        return array_key_exists($this->getKey(), $array);
    }

    /**
     * A tag to identify misc property settings. (IE: For db set 'key' to identify the primary key property(s))
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * A tag to identify misc property settings. (IE: For db set 'key' to identify the primary key property(s))
     */
    public function setAttributes(array $attributes): static
    {
        $this->attributes = $attributes;
        return $this;
    }

    public function getAttribute(string $name): string
    {
        return $this->attributes[$name] ?? '';
    }

    public function hasAttribute(string $name): bool
    {
        return array_key_exists($name, $this->attributes);
    }

    public function setAttribute(string $name, ?string $value = null): DataTypeInterface
    {
        if ($value === null && isset($this->attributes[$name])) {
            unset($this->attributes[$name]);
        } else {
            $this->attributes[$name] = $value;
        }
        return $this;
    }

}