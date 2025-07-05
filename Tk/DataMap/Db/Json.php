<?php
namespace Tk\DataMap\Db;

use Tk\DataMap\DataTypeInterface;

/**
 * map a JSON string type from a DB field to an object property
 * @see https://www.php.net/manual/en/function.json-decode.php
 */
class Json extends DataTypeInterface
{
    /**
     * if true then the returned value from json_decode will be an array
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
        if ($this->isNullable() && empty($value)) return null;
        return json_decode($value, $this->associative);
    }

    public function getColumnValue(object $object): mixed
    {
        $value = parent::getColumnValue($object);
        // Fixes bug where json_encode returns an array object instead of a string for empty arrays
        if ($this->associative && is_array($value) && !count($value)) $value = '';
        if ($this->isNullable() && empty($value)) return null;
        return strval(json_encode($value, JSON_UNESCAPED_SLASHES));
    }

}