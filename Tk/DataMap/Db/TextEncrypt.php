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
        if (!is_null($value)) {
            $value = Encrypt::create(self::$encryptKey)->decrypt(strval($value));
        } elseif (!$this->isNullable()) {
            $value = '';
        }
        return $value;
    }

    public function getColumnValue(object $object): mixed
    {
        $value = parent::getColumnValue($object);
        if (!is_null($value)) {
            $value = Encrypt::create(self::$encryptKey)->encrypt(strval($value));
        } elseif (!$this->isNullable()) {
            $value = '';
        }
        return $value;
    }

}

