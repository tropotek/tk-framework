<?php
namespace Tk\DataMap\Db;

use Tk\DataMap\DataTypeInterface;

/**
 * map an array type from a DB field to an object property
 */
class ArrayType extends DataTypeInterface
{
    /**
     * if true then the returned array will have values as keys
     */
    protected bool $associative = false;

    public function setAssociative(bool $b): static
    {
        $this->associative = $b;
        return $this;
    }

    public function getPropertyValue(array $array): mixed
    {
        $value = parent::getPropertyValue($array);
        if (is_string($value)) $value = explode(',', $value);
        if (is_null($value)) { $value = []; }
        if ($this->associative) {
            $value = array_combine($value, $value);
        }
        return $value;
    }

    public function getColumnValue(object $object): mixed
    {
        $value = parent::getColumnValue($object);
        if (is_array($value)) $value = implode(',', $value);
        return $value;
    }

}