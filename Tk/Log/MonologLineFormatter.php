<?php
namespace Tk\Log;

use Monolog\Formatter\LineFormatter;

/**
 * Class LogLineFormatter
 *
 * @author Michael Mifsud <http://www.tropotek.com/>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class MonologLineFormatter extends LineFormatter
{
    //const APP_FORMAT = "[%datetime%]%post% %channel%.%level_name%: %message% %context% %extra%\n";
    const APP_FORMAT = "[%datetime%]%post% %level_name%: %message% %context% %extra%\n";

    protected $scriptTime = 0;

    protected $colorsEnabled = false;

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
    public function format(array $record) :string
    {
        $colors = array(
            'emergency'     => 'brown',
            'alert'         => 'yellow',
            'critical'      => 'red',
            'error'         => 'light_red',
            'warning'       => 'light_cyan',

            'notice'        => 'light_purple',
            'info'          => 'white',
            'debug'         => 'light_gray'
        );
        $abbrev = array(
            'emergency'     => 'EMR',
            'alert'         => 'ALT',
            'critical'      => 'CRT',
            'error'         => 'ERR',
            'warning'       => 'WRN',
            'notice'        => 'NTC',
            'info'          => 'INF',
            'debug'         => 'DBG'
        );

        $levelName = $record['level_name'];
        $record['level_name'] = $abbrev[strtolower($levelName)];
        
        if ($this->isColorsEnabled())
            $record['message'] = \Tk\Color::getCliString($record['message'], $colors[strtolower($levelName)]);

        $output = parent::format($record);
        $pre = sprintf('[%9s]', \Tk\File::bytes2String(memory_get_usage(false)));
        $output = str_replace('%post%', $pre, $output);
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

    /**
     * @return bool
     */
    public function isColorsEnabled()
    {
        return $this->colorsEnabled;
    }

    /**
     * @param bool $colorsEnabled
     * @return $this
     */
    public function setColorsEnabled($colorsEnabled)
    {
        $this->colorsEnabled = $colorsEnabled;
        return $this;
    }

}