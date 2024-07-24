<?php
namespace Tt\DataMap\Db;

use Tt\DataMap\DataTypeInterface;

/**
 * map a serialized type from a DB field to an object property
 */
class TextEncrypt extends DataTypeInterface
{
    public static string $encryptKey = '';


    public function getKeyValue(array $array): mixed
    {
        $value = parent::getKeyValue($array);
        if ($value) {
            $value = \Tk\Encrypt::create(self::$encryptKey)->decrypt($value);
        }
        return $value;
    }

    public function getPropertyValue(object $object): mixed
    {
        $value = parent::getPropertyValue($object);
        if ($value) {
            $value = \Tk\Encrypt::create(self::$encryptKey)->encrypt($value);
        }
        return $value;
    }

}

