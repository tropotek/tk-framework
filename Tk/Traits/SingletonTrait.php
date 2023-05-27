<?php
namespace Tk\Traits;

trait SingletonTrait
{
    private static $_INSTANCE = null;

    /**
     * Gets an instance of this object, if none exists one is created
     */
    public static function instance(): static
    {
        if (self::$_INSTANCE == null) {
            self::$_INSTANCE = new static();
        }
        return self::$_INSTANCE;
    }
}