<?php
namespace Tk\Traits;

trait DataTrait
{
    protected array $_data = [];


    public function __set(string $name, mixed $value)
    {
        $this->_data[$name] = $value;
    }

    public function __get(string $name): mixed
    {
        return $this->_data[$name] ?? null;
    }
}