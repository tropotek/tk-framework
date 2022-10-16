<?php
namespace Tk\Log;

use Monolog\Formatter\LineFormatter;
use Tk\Log;
use Tk\Traits\FactoryTrait;

/**
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class MonologLineFormatter extends LineFormatter
{
    use FactoryTrait;

    const APP_FORMAT = "[%datetime%]%post% %level_name%: %message% %context% %extra%\n";

    protected int $scriptTime = 0;

    protected bool $colorsEnabled = false;


    public function __construct(?string $format = null, ?string $dateFormat = 'H:i:s.u', bool $allowInlineLineBreaks = true, bool $ignoreEmptyContextAndExtra = true)
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
        if ($this->getFactory()->getRequest()->query->has(Log::NO_LOG)) return '';
        $colors = array(
            'emergency'     => 'Brown',
            'alert'         => 'Yellow',
            'critical'      => 'Red',
            'error'         => 'LightRed',
            'warning'       => 'LightCyan',
            'notice'        => 'LightPurple',
            'info'          => 'White',
            'debug'         => 'LightGray'
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
        $pre = sprintf('[%9s]', \Tk\FileUtil::bytes2String(memory_get_usage(false)));
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