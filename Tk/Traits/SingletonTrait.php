<?php

namespace Tk\Traits;

trait SingletonTrait
{
    private static mixed $_instance = null;


    final protected function __construct() { }

    /**
     * Gets an instance of this object, if none exists one is created
     */
    public static function instance(): static
    {
        if (self::$_instance == null) {
            self::$_instance = new static();
            self::$_instance->_init();
        }
        return self::$_instance;
    }

    /**
     * For singletons the constructor must be set to final
     * Use the _init() method to init you singleton object
     */
    protected function _init(): void
    {

    }
}