<?php

namespace Tt\Table\Type;

use Tt\Table\Cell;

class DateTime
{
    //public static string $format = 'j M Y H:i:s';
    public static string $format = 'j M Y g:ia';

    public static function onValue(array|object $row, Cell $cell): string
    {
        $value = $row->{$cell->getName()};
        if ($value instanceof \DateTimeInterface) {
            $value = $value->format(self::$format);
        }
        return $value ?? '';
    }

}