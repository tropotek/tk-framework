<?php
namespace Tk\Traits;

/**
 * Implements an interface for the PHP magic methods __get and __set
 * enable the ability to set a non-existing object property for PHP5.3+
 */
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