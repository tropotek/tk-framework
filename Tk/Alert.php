<?php

namespace Tk;

use Tk\Traits\SystemTrait;

class Alert
{
    use SystemTrait;

    const TYPE_SUCCESS = 'success';
    const TYPE_INFO = 'info';
    const TYPE_WARNING = 'warning';
    const TYPE_ERROR = 'danger';

    /**
     * add a flash message to the renderer queue in the session
     * Note the message is a serialized object of the parameters that needs to be un-serialized for rendering
     * @return object  The param object saved
     */
    public static function add(string $message, string $type = self::TYPE_SUCCESS, string $title = '', string $icon = ''): object
    {
        $a = (object)compact('message', 'type', 'title', 'icon');
        Factory::instance()->getSession()->getFlashBag()->add($type, serialize($a));
        return $a;
    }

    public static function addSuccess(string $message, string $title = '', string $icon = ''): object
    {
        return self::add($message, self::TYPE_SUCCESS, $title, $icon);
    }

    public static function addWarning(string $message, string $title = '', string $icon = ''): object
    {
        return self::add($message, self::TYPE_WARNING, $title, $icon);
    }

    public static function addError(string $message, string $title = '', string $icon = ''): object
    {
        return self::add($message, self::TYPE_ERROR, $title, $icon);
    }

    public static function addInfo(string $message, string $title = '', string $icon = ''): object
    {
        return self::add($message, self::TYPE_INFO, $title, $icon);
    }

}