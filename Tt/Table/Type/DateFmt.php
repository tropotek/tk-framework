<?php

namespace Tt\Table\Type;

use Tt\Table\Cell;

class DateFmt
{
    public static string $format = 'j M Y';

    public static function onValue(array|object $row, Cell $cell): string
    {
        $value = $row->{$cell->getName()};
        if ($value instanceof \DateTimeInterface) {
            $value = $value->format(self::$format);
        }
        return $value ?? '';
    }

}