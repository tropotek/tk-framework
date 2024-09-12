<?php
namespace Tk\Traits;

trait DataTrait
{
    protected array $_data = [];


    public function __set($name, $value)
    {
        $this->_data[$name] = $value;
    }

    public function __get($name): mixed
    {
        return $this->_data[$name] ?? null;
    }
}