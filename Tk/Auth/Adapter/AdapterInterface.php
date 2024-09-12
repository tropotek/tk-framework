<?php
namespace Tk\Auth\Adapter;

use Tk\Auth\Result;

abstract class AdapterInterface
{
    /**
     * Perform an authentication attempt
     */
    public abstract function authenticate(): Result;

}