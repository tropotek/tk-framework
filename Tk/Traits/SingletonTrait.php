<?php
namespace Tk\Traits;

/**
 * @deprecated
 */
trait SingletonTrait
{
    protected static mixed $_instance = null;

    /**
     * Gets an instance of this object, if none exists one is created
     */
    public static function instance(): static
    {
        print_r('HELLLL NOOOOOOOOOOOOOOO!!!');
        if (is_null(self::$_instance)) {
            self::$_instance = new static();
        }
        return self::$_instance;
    }
}