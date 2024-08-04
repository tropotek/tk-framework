<?php

namespace Tt\Table\Type;

use Tt\Table\Cell;

class Time
{
    /**
     * alt format 'H:i:s' 24 hour time or 'g:ia' for meridian time
     */
    public static string $format = 'H:i';

    public static function onValue(array|object $row, Cell $cell): string
    {
        $value = $row->{$cell->getName()};
        if ($value instanceof \DateTimeInterface) {
            $value = $value->format(self::$format);
        }
        return $value ?? '';
    }

}