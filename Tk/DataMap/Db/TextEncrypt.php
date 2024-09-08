<?php
namespace Tk\DataMap\Db;

use Tk\DataMap\DataTypeInterface;

/**
 * map a serialized type from a DB field to an object property
 */
class TextEncrypt extends DataTypeInterface
{
    public static string $encryptKey = '';


    public function getPropertyValue(array $array): mixed
    {
        $value = parent::getPropertyValue($array);
        if (!is_null($value)) {
            $value = \Tk\Encrypt::create(self::$encryptKey)->decrypt(strval($value));
        }
        return $value;
    }

    public function getColumnValue(object $object): mixed
    {
        $value = parent::getColumnValue($object);
        if (!is_null($value)) {
            $value = \Tk\Encrypt::create(self::$encryptKey)->encrypt(strval($value));
        }
        return $value;
    }

}

