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

    protected $scriptTime = 0;

    /**
     * @param string $format                     The format of the message
     * @param string $dateFormat                 The format of the timestamp: one supported by DateTime::format
     * @param bool   $allowInlineLineBreaks      Whether to allow inline line breaks in log entries
     * @param bool   $ignoreEmptyContextAndExtra
     */
    public function __construct($format = null, $dateFormat = 'Y-m-d H:i:s', $allowInlineLineBreaks = true, $ignoreEmptyContextAndExtra = true)
    {
        $this->scriptTime = microtime(true);
        $format = $format ?: static::APP_FORMAT;
        parent::__construct($format, $dateFormat, $allowInlineLineBreaks, $ignoreEmptyContextAndExtra);
    }

    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        $output = parent::format($record);

        $pre = sprintf('[%5.2f][%8s]', round($this->scriptDuration(), 2), \Tk\File::bytes2String(memory_get_usage(false)));
        $output = str_replace('%pre%', $pre, $output);

        return $output;
    }

    /**
     * @param $t
     */
    public function setScripTime($t)
    {
        $this->scriptTime = $t;
    }

    /**
     * Get the current script running time in seconds
     *
     * @return string
     */
    public function scriptDuration()
    {
        return (string)(microtime(true) - $this->scriptTime);
    }

}