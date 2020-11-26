<?php
/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk;

/**
 * The DateRime utilities
 *
 */
class Date
{

    /**
     * Use this to format form dates, change it in the script bootstrap if required
     *
     * @var string
     */
    public static $formFormat = 'd/m/Y';

    /**
     * EG: 2009-12-31 24:59:59
     */
    const FORMAT_ISO_DATETIME = 'Y-m-d H:i:s';

    /**
     * EG: 2009-12-31
     */
    const FORMAT_ISO_DATE = 'Y-m-d';

    /**
     * EG: Tuesday, 23 Apr 2009
     */
    const FORMAT_LONG_DATE = 'l, j M Y';

    /**
     * EG: Tuesday, 01 Jan 2009 12:59 PM
     */
    const FORMAT_LONG_DATETIME = 'l, j M Y h:i A';

    /**
     * EG: 23/09/2009 24:59:59
     */
    const FORMAT_SHORT_DATETIME = 'd/m/Y H:i:s';

    /**
     * EG: 23/09/2009
     */
    const FORMAT_SHORT_DATE = 'd/m/Y';

    /**
     * EG: 23 Apr 2009
     */
    const FORMAT_MED_DATE = 'j M Y';


    /**
     * EG: 2009-12-31 24:59:59
     * @deprecated prefix with FORMAT_
     */
    const ISO_DATE = 'Y-m-d H:i:s';

    /**
     * EG: Tuesday, 23 Apr 2009
     * @deprecated prefix with FORMAT_
     */
    const LONG_DATE = 'l, j M Y';

    /**
     * EG: Tuesday, 01 Jan 2009 12:59 PM
     * @deprecated prefix with FORMAT_
     */
    const LONG_DATETIME = 'l, j M Y h:i A';

    /**
     * EG: 23/09/2009 24:59:59
     * @deprecated prefix with FORMAT_
     */
    const SHORT_DATETIME = 'd/m/Y H:i:s';

    /**
     * EG: 23 Apr 2009
     * @deprecated prefix with FORMAT_
     */
    const MED_DATE = 'j M Y';

    /**
     * An hour in seconds (60*60)
     */
    const HOUR = 3600;

    /**
     * A Day in seconds (HOUR*24)
     */
    const DAY = 86400;

    /**
     * A Week in seconds (DAY*7)
     */
    const WEEK = 604800;
    

    /**
     * Month end days.
     * @var array
     */
    private static $monthEnd = array('1' => '31', '2' => '28', '3' => '31',
        '4' => '30', '5' => '31', '6' => '30', '7' => '31', '8' => '31',
        '9' => '30', '10' => '31', '11' => '30', '12' => '31');


    /**
     * __construct
     * no instances to be created
     */
    private function __construct() { }


    /**
     * Create a DateTime object with system timezone
     *
     * @param string $time
     * @param \DateTimeZone $timezone
     * @return \DateTime
     */
    static function create($time = 'now', $timezone = null)
    {
        if ($time instanceof \DateTime) return $time;
        if ($timezone && is_string($timezone)) {
            $timezone = new \DateTimeZone($timezone);
        }
        if (!$timezone) {
            $timezone = new \DateTimeZone(date_default_timezone_get());
        }
        
        if (preg_match('/^[0-9]{1,11}$/', $time)) {
            $time = '@'.$time;
        }

        try {
            $date = new \DateTime($time, $timezone);
            $date->setTimezone($timezone);
            return $date;
        } catch (\Exception $e) {
            \Tk\Log::error($e->getMessage());
        }
    }

    /**
     * Create a date from a string returned from the self::$formFormat string
     *
     * @param string $dateStr
     * @param null|string $format
     * @param null $timezone
     * @return \DateTime
     */
    static function createFormDate($dateStr, $timezone = null, $format = null)
    {
        if (!$dateStr) return null;
        if ($timezone && is_string($timezone)) {
            $timezone = new \DateTimeZone($timezone);
        }
        if (!$timezone) {
            $timezone = new \DateTimeZone(date_default_timezone_get());
        }
        if (!$format) {
            $format = self::$formFormat;
        }
        $date = \DateTime::createFromFormat($format, $dateStr);
        if (!$date && str_ends_with($dateStr, 'Z')) {   // could be ISO format of: 2020-11-23T01:20:27.164Z
            $date = new \DateTime($dateStr);
        }
        if ($date) {
            $date->setTimezone($timezone);
        }
        return $date;

    }



    /**
     * Get the months ending date 1 = 31-Jan, 12 = 31-Dec
     *
     * @param int $m
     * @param int $y
     * @return int
     */
    static function getMonthDays($m, $y = 0)
    {
        if ($m == 2) { // feb test for leap year
            if (self::isLeapYear($y)) {
                return self::$monthEnd[$m] + 1;
            }
        }
        return self::$monthEnd[$m];
    }

    /**
     * Is the supplied year a leap year
     *
     * @param int $y
     * @return bool
     */
    static function isLeapYear($y)
    {
        if ($y % 4 != 0) {
            return false;
        } else if ($y % 400 == 0) {
            return true;
        } else if ($y % 100 == 0) {
            return false;
        } else {
            return true;
        }
    }


    /**
     * Get the financial year of this date
     * list($start, $end) = Tk\Date::getFinancialYear($date);
     *
     * @param \DateTime $date
     * @return \DateTime[]
     */
    static function getFinancialYear(\DateTime $date = null)
    {
        if (!$date) $date = self::create();
        $year = (int)$date->format('Y');
        $month = (int)$date->format('n');
        if ($month < 7) {
            $year--;
        }
        $start = new \DateTime($year.'-07-01 00:00:00', $date->getTimezone());
        $end = new \DateTime(($year+1).'-06-30 23:59:59', $date->getTimezone());
        
        return array($start, $end);
    }


    /**
     * Set the time of a date object to 23:59:59
     *
     * @param \DateTime $date
     * @return \DateTime
     */
    static function ceil(\DateTime $date = null)
    {
        if (!$date) $date = self::create();
        return new \DateTime($date->format('Y-m-d 23:59:59'), $date->getTimezone());
    }

    /**
     * Set the time of a date object to 00:00:00
     *
     * @param \DateTime $date
     * @return \DateTime
     */
    static function floor(\DateTime $date = null)
    {
        if (!$date) $date = self::create();
        return new \DateTime($date->format('Y-m-d 00:00:00'), $date->getTimezone());
    }

    /**
     * Get the first day of this dates month
     *
     * @param \DateTime $date
     * @return \DateTime
     */
    static function getMonthStart(\DateTime $date = null)
    {
        if (!$date) $date = self::create();
        return new \DateTime($date->format('Y-m-01 00:00:00'), $date->getTimezone());
    }

    /**
     * Get the last day of this dates month
     *
     * @param \DateTime $date
     * @return \DateTime
     */
    static function getMonthEnd(\DateTime $date = null)
    {
        if (!$date) $date = self::create();
        $lastDay = self::getMonthDays($date->format('n'), $date->format('Y'));
        return new \DateTime($date->format('Y-m-'.$lastDay.' 23:59:59'), $date->getTimezone());
    }


    /**
     * Get the first day of this dates month
     *
     * @param \DateTime $date
     * @return \DateTime
     */
    static function getYearStart(\DateTime $date = null)
    {
        if (!$date) $date = self::create();
        return new \DateTime($date->format('Y-01-01 00:00:00'), $date->getTimezone());
    }

    /**
     * Get the last day of this dates month
     *
     * @param \DateTime $date
     * @return \DateTime
     */
    static function getYearEnd(\DateTime $date = null)
    {
        if (!$date) $date = self::create();
        return new \DateTime($date->format('Y-12-31 23:59:59'), $date->getTimezone());
    }
    
    
    
    
    
    /**
     * Returns the difference between this date and other in days.
     *
     * @param \DateTime $from
     * @param \DateTime $to
     * @return int
     */
    static function dayDiff(\DateTime $from, \DateTime $to)
    {
        return ceil(($from->getTimestamp() - $to->getTimestamp()) / self::DAY);
    }

    /**
     * Return the difference between this date and other in hours.
     *
     * @param \DateTime $from
     * @param \DateTime $to
     * @return int
     */
    static function hourDiff(\DateTime $from, \DateTime $to)
    {
        return ceil(($from->getTimestamp() - $to->getTimestamp()) / self::HOUR);
    }
    
    /**
     * Compares the value to another instance of date.
     *
     * @param \DateTime $from
     * @param \DateTime $to
     * @return int Returns -1 if less than , 0 if equal to, 1 if greater than.
     */
    static function compareTo(\DateTime $from, \DateTime $to)
    {
        $retVal = 1;
        if ($from->getTimestamp() < $to->getTimestamp()) {
            $retVal = -1;
        } elseif ($from->getTimestamp() == $to->getTimestamp()) {
            $retVal = 0;
        }

        return $retVal;
    }

    /**
     * Checks if the $from Date is greater than the $to Date
     *
     * @param \DateTime $from
     * @param \DateTime $to
     * @return bool
     */
    static function greaterThan(\DateTime $from, \DateTime $to)
    {
        return (self::compareTo($from, $to) > 0);
    }
    
    /**
     * Checks if the date value is greater than or equal the value of another instance of date.
     *
     * @param \DateTime $from
     * @param \DateTime $to
     * @return bool
     */
    static function greaterThanEqual(\DateTime $from, \DateTime $to)
    {
        return (self::compareTo($from, $to) >= 0);
    }

    /**
     * Checks if the date value is less than the value of another instance of date.
     *
     * @param \DateTime $from
     * @param \DateTime $to
     * @return bool
     */
    static function lessThan(\DateTime $from, \DateTime $to)
    {
        return (self::compareTo($from, $to) < 0);
    }

    /**
     * Checks if the date value is less than or equal the value of another instance of date.
     *
     * @param \DateTime $from
     * @param \DateTime $to
     * @return bool
     */
    static function lessThanEqual(\DateTime $from, \DateTime $to)
    {
        return (self::compareTo($from, $to) <= 0);
    }

    /**
     * Checks if the date is equal to the value of another instance of date.
     *
     * @param \DateTime $from
     * @param \DateTime $to
     * @return bool
     */
    static function equals(\DateTime $from, \DateTime $to)
    {
        return (self::compareTo($from, $to) == 0);
    }

    /*
     * convert a date into a string that tells how long
     * ago that date was.... eg: 2 days ago, 3 minutes ago.
     *
     * Note: Only works for dates in the past...
     *
     * @param \DateTime $date
     * @return string
     */
    static function toRelativeString(\DateTime $date = null)
    {
        if (!$date) $date = self::create();
        $c = getdate();
        $p = array('year', 'mon', 'mday', 'hours', 'minutes', 'seconds');
        $display = array('year', 'month', 'day', 'hour', 'minute', 'second');
        $factor = array(0, 12, 30, 24, 60, 60);
        preg_match("/([0-9]{4})(\\-)([0-9]{2})(\\-)([0-9]{2}) ([0-9]{2})(\\:)([0-9]{2})(\\:)([0-9]{2})/", $date->format(self::FORMAT_ISO_DATETIME), $matches);
        $d = array(
            'seconds' => $matches[10],
            'minutes' => $matches[8],
            'hours' => $matches[6],
            'mday' => $matches[5],
            'mon' => $matches[3],
            'year' => $matches[1],
        );

        for ($w = 0; $w < 6; $w++) {
            if ($w > 0) {
                $c[$p[$w]] += $c[$p[$w-1]] * $factor[$w];
                $d[$p[$w]] += $d[$p[$w-1]] * $factor[$w];
            }
            if ($c[$p[$w]] - $d[$p[$w]] > 1) {
                return ($c[$p[$w]] - $d[$p[$w]]).' '.$display[$w].'s ago';
            }
        }
        return 'Now';
    }
}
