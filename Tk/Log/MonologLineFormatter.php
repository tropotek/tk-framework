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
    const APP_FORMAT = "[%datetime%]%post% %channel%.%level_name%: %message% %context% %extra%\n";

    protected $scriptTime = 0;

    /**
     * @param string $format                     The format of the message
     * @param string $dateFormat                 The format of the timestamp: one supported by DateTime::format
     * @param bool   $allowInlineLineBreaks      Whether to allow inline line breaks in log entries
     * @param bool   $ignoreEmptyContextAndExtra
     */
    public function __construct($format = null, $dateFormat = 'H:i:s.u', $allowInlineLineBreaks = true, $ignoreEmptyContextAndExtra = true)
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

        $colors = array(
            'emergency' => 'red',
            'alert' => 'light_cyan',
            'critical' => 'light_red',
            'error' => 'light_red',
            'warning' => 'yellow',
            'notice' => 'light_purple',
            'info' => 'white',
            'debug' => 'light_gray'
        );

        //error_log(print_r($record, true));
        $levelName = $record['level_name'];
        $record['level_name'] = $levelName[0];
        $record['message'] = \Tk\Color::getCliString($record['message'], $colors[strtolower($levelName)]);

        $output = parent::format($record);
        //$pre = sprintf('[%5.2f][%9s]', round($this->scriptDuration(), 2), \Tk\File::bytes2String(memory_get_usage(false)));
        $pre = sprintf('[%9s]', \Tk\File::bytes2String(memory_get_usage(false)));
        $output = str_replace('%post%', $pre, $output);
        //return \Tk\Color::getCliString($output, 'white');
        return $output;
    }

    /**
     * @param $t
     * @return $this
     */
    public function setScriptTime($t)
    {
        if ($t)
            $this->scriptTime = $t;
        return $this;
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