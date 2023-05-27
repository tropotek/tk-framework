<?php
namespace Tk\DataMap\Db;

use Tk\DataMap\DataTypeInterface;

/**
 * map a serialized type from a DB field to an object property
 */
class TextEncrypt extends DataTypeInterface
{

    private string $encryptKey = '';


    public function setEncryptKey(string $key): TextEncrypt
    {
        $this->encryptKey = $key;
        return $this;
    }

    public function getKeyValue(array $array): mixed
    {
        $value = parent::getKeyValue($array);
        if ($value) {
            $value = \Tk\Encrypt::create($this->encryptKey)->decrypt($value);
        }
        return $value;
    }

    public function getPropertyValue(object $object): mixed
    {
        $value = parent::getPropertyValue($object);
        if ($value) {
            $value = \Tk\Encrypt::create($this->encryptKey)->encrypt($value);
        }
        return $value;
    }

}

