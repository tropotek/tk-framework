<?php
namespace Tk\Log;

use Monolog\Formatter\LineFormatter;

/**
 * Class LogLineFormatter
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class MonologLineFormatter extends LineFormatter
{
    const APP_FORMAT = "[%datetime%]%pre% %channel%.%level_name%: %message% %context% %extra%\n";

    /**
     * @param string $format                     The format of the message
     * @param string $dateFormat                 The format of the timestamp: one supported by DateTime::format
     * @param bool   $allowInlineLineBreaks      Whether to allow inline line breaks in log entries
     * @param bool   $ignoreEmptyContextAndExtra
     */
    public function __construct($format = null, $dateFormat = 'Y-m-d H:i:s', $allowInlineLineBreaks = true, $ignoreEmptyContextAndExtra = true)
    {
        $format = $format ?: static::APP_FORMAT;
        parent::__construct($format, $dateFormat, $allowInlineLineBreaks, $ignoreEmptyContextAndExtra);
    }

    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        $output = parent::format($record);

        $pre = sprintf('[%5.2f][%8s]', round(\Tk\Config::scriptDuration(), 2), self::bytes2String(memory_get_usage(false)));
        $output = str_replace('%pre%', $pre, $output);

        return $output;
    }

    /**
     * Convert a value from bytes to a human readable value
     *
     * @param int $bytes
     * @return string
     * @author http://php-pdb.sourceforge.net/samples/viewSource.php?file=twister.php
     */
    static function bytes2String($bytes, $round = 2)
    {
        $tags = array('b', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $index = 0;
        while ($bytes > 999 && isset($tags[$index + 1])) {
            $bytes /= 1024;
            $index++;
        }
        $rounder = 1;
        if ($bytes < 10) {
            $rounder *= 10;
        }
        if ($bytes < 100) {
            $rounder *= 10;
        }
        $bytes *= $rounder;
        settype($bytes, 'integer');
        $bytes /= $rounder;
        if ($round > 0) {
            $bytes = round($bytes, $round);
            return sprintf('%.' . $round . 'f %s', $bytes, $tags[$index]);
        } else {
            return sprintf('%s %s', $bytes, $tags[$index]);
        }
    }
}