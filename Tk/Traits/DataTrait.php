<?php
namespace Tk\Traits;

use Tk\Exception;

trait DataTrait
{
    protected array $_data = [];


    public function __set($name, $value)
    {
        $this->_data[$name] = $value;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->_data)) return $this->_data[$name];
        throw new Exception("Undefined property name '{$name}'");
    }
}