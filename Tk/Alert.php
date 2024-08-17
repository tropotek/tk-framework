<?php

namespace Tk;

use Tk\Traits\SystemTrait;

class Alert
{
    use SystemTrait;

    const SID = 'tk-alerts';

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
        self::saveAlert($type, serialize($a));
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


    protected static function saveAlert($type, $data): void
    {
        $_SESSION[self::SID][$type][] = $data;
    }

    public static function getAlerts(bool $clear = true): array
    {
        $list = $_SESSION[self::SID] ?? [];
        if ($clear) unset($_SESSION[self::SID]);
        foreach ($list as $type => $arr) {
            foreach ($arr as $i => $data) {
                $list[$type][$i] = unserialize($data);
            }
        }
        return $list;
    }

    public static function hasAlerts(): bool
    {
        return count($_SESSION[self::SID] ?? []) > 0;
    }

}