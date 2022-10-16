<?php
namespace Tk\DataMap\Db;

use Tk\DataMap\DataTypeIface;

/**
 * map a serialized type from a DB field to an object property
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class TextEncrypt extends DataTypeIface
{

    private string $encryptKey = '';


    public function setEncryptKey(string $key): TextEncrypt
    {
        $this->encryptKey = $key;
        return $this;
    }

    public function getKeyValue(array $array)
    {
        $value = parent::getKeyValue($array);
        if ($value) {
            $value = \Tk\Encrypt::create($this->encryptKey)->decrypt($value);
        }
        return $value;
    }

    public function getPropertyValue(object $object)
    {
        $value = parent::getPropertyValue($object);
        if ($value) {
            $value = \Tk\Encrypt::create($this->encryptKey)->encrypt($value);
        }
        return $value;
    }
    
}

