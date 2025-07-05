<?php
namespace Tk\DataMap\Db;

use Tk\DataMap\DataTypeInterface;
use Tk\Encrypt;

/**
 * map a serialized type from a DB field to an object property
 */
class TextEncrypt extends DataTypeInterface
{
    public static string $encryptKey = '';


    public function getPropertyValue(array $array): mixed
    {
        $value = parent::getPropertyValue($array);
        if ($this->isNullable() && empty($value)) return null;
        return Encrypt::create(self::$encryptKey)->decrypt(strval($value));
    }

    public function getColumnValue(object $object): mixed
    {
        $value = parent::getColumnValue($object);
        if ($this->isNullable() && is_null($value)) return null;
        return Encrypt::create(self::$encryptKey)->encrypt(strval($value));
    }

}

