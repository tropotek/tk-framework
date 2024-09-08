<?php
namespace Tk\DataMap\Db;

use Tk\DataMap\DataTypeInterface;

/**
 * map a JSON string type from a DB field to an object property
 */
class Json extends DataTypeInterface
{
    /**
     * if true then the returned value from json_decode will ba an array
     */
    protected ?bool $associative = null;

    public function setAssociative(bool $b): Json
    {
        $this->associative = $b;
        return $this;
    }

    public function getPropertyValue(array $array): mixed
    {
        $value = parent::getPropertyValue($array);
        if (!is_null($value)) {
            $value = json_decode($value, $this->associative);
        }
        return $value;
    }

    public function getColumnValue(object $object): mixed
    {
        $value = parent::getColumnValue($object);
        // Fixes bug where json_encode returns an array object instead of a string for empty arrays
        if ($this->associative && is_array($value) && !count($value)) return '';
        if (!is_null($value)) {
            $value = json_encode($value) ?? '';
        }
        return $value;
    }

}