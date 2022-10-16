<?php
namespace Tk\DataMap;


use Tk\ObjectUtil;

/**
 * @author Tropotek <http://www.tropotek.com/>
 */
abstract class DataTypeIface
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
     *
     * @return mixed|null
     */
    public function getPropertyValue(object $object)
    {
        $v = null;
        if ($this->hasProperty($object)) {
            $v = ObjectUtil::getObjectPropertyValue($object, $this->getProperty());
        }
        return $v;
    }

    /**
     * Return the key value from the array supplied
     *
     * @return mixed|null
     */
    public function getKeyValue(array $array)
    {
        if (isset($array[$this->getKey()])) {
            return $array[$this->getKey()];
        }
        return null;
    }

    /**
     * Set the objects property from the supplied array values
     */
    public function loadObject(object $object, array $srcArray): DataTypeIface
    {
        $value = $this->getKeyValue($srcArray, $this->getKey());
        ObjectUtil::setPropertyValue($object, $this->getProperty(), $value);
        return $this;
    }

    /**
     * Set the array key/value from the object`s property
     */
    public function loadArray(array &$array, object $srcObject): DataTypeIface
    {
        $value = $this->getPropertyValue($srcObject, $this->getProperty());
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
        if (is_array($array))
            return array_key_exists($this->getKey(), $array);
        return false;
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
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    public function getAttribute(string $name): string
    {
        return $this->attributes[$name] ?? '';
    }

    public function hasAttribute(string $name): bool
    {
        return array_key_exists($name, $this->attributes);
    }

    public function setAttribute(string $name, ?string $value = null): DataTypeIface
    {
        if ($value === null && isset($this->attributes[$name])) {
            unset($this->attributes[$name]);
        } else {
            $this->attributes[$name] = $value;
        }
        return $this;
    }

}