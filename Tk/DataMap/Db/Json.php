<?php
namespace Tk\DataMap\Db;

use Tk\DataMap\DataTypeIface;

/**
 * map a JSON string type from a DB field to an object property
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class Json extends DataTypeIface
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

    public function getKeyValue(array $array)
    {
        $value = parent::getKeyValue($array);
        if ($value) {
            $value = json_decode($value, $this->associative);
        }
        return $value;
    }

    public function getPropertyValue(object $object)
    {
        $value = parent::getPropertyValue($object);
        // Fixes bug where json_encode returns an array object instead of a string for empty arrays
        if ($this->associative && is_array($value) && !count($value)) return '';
        if ($value) {
            $value = json_encode($value) ?? '';
        }
        return $value;
    }
    
}

