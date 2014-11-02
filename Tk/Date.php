<?php
/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk;

/**
 * The Date object to handle date functions.
 *
 * @package Tk
 */
class Date extends \DateTime
{

    /**
     * EG: 2009-12-31 24:59:59
     */
    const ISO_DATE = 'Y-m-d H:i:s';

    /**
     * EG: Tuesday, 23 Apr 2009
     */
    const LONG_DATE = 'l, j M Y';

    /**
     * EG: Tuesday, 01 Jan 2009 12:59 PM
     */
    const LONG_DATETIME = 'l, j M Y h:i A';

    /**
     * EG: 23/09/2009 24:59:59
     */
    const SHORT_DATETIME = 'd/m/Y H:i:s';

    /**
     * EG: 23 Apr 2009
     */
    const MED_DATE = 'j M Y';


    /**
     * Month end days.
     * @var array
     */
    private static $monthEnd = array('1' => '31', '2' => '28', '3' => '31',
        '4' => '30', '5' => '31', '6' => '30', '7' => '31', '8' => '31',
        '9' => '30', '10' => '31', '11' => '30', '12' => '31');


    /**
     * __construct
     *
     * @param string $time
     * @param \DateTimeZone $timezone
     */
    public function __construct($time = 'now', \DateTimeZone $timezone = null)
    {
        if ($timezone && !$timezone instanceof \DateTimeZone) {
            $timezone = new \DateTimeZone($timezone);
        }
        if (!$timezone) {
            $timezone = new \DateTimeZone(date_default_timezone_get());
        }

        // TODO: Need to create a date format object for converting and displaying varing date formats
        $regs = null;
        if (preg_match('/^([0-9]{1,2})(\/|-)([0-9]{1,2})(\/|-)([0-9]{2,4})( ([0-9]{1,2}):([0-9]{1,2})(:([0-9]{1,2}))?)?$/', $time, $regs)) {
            $day = date('j');
            $month = date('n');
            $year = date('Y');
            $hour = date('G');
            $minute = date('i');
            $second = date('s');

            $day = intval($regs[1]);
            $month = intval($regs[3]);
            $year = intval($regs[5]);

            if (isset($regs[6])) {
                //$time = \DateTime::createFromFormat('Y-m-d H:i:s', $time, $timezone)->getTimestamp();
                $hour = intval($regs[6]);
                $minute = intval($regs[7]);
                if (isset($regs[9])) {
                    $second = intval($regs[9]);
                }
            }

//            else {
                  //TODO: need to cater for 23/12/2014 type dates
//                $time = \DateTime::createFromFormat('Y-m-d', $time, $timezone)->getTimestamp();
//            }
            $time = '@'.mktime($hour, $minute, $second, $month, $day, $year);
        }
        if (preg_match('/^[0-9]{1,11}$/', $time)) {
            $time = '@'.$time;
        }
        if ($timezone) {
            parent::__construct($time, $timezone);
            $this->setTimezone($timezone);
        } else {
            parent::__construct ($time);
        }
    }

    /**
     * Create a date from a static context.
     *
     * @param string $time
     * @param \DateTimeZone $timezone
     * @return Date
     */
    static function create($time = 'now', \DateTimeZone $timezone = null)
    {
        return new self($time, $timezone);
    }


    /**
     * Get the months ending date 1 = 31-Jan, 12 = 31-Dec
     *
     * @param int $m
     * @param int $y
     * @return int
     */
    static function getMonthDays($m, $y = '')
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
     * @param bool
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
     * list($start, $end) = Tk\Date::create()->getFinancialYear();
     *
     * @return array
     * @todo Put this in a new Tk\Date Set object that manages time spans
     */
    public function getFinancialYear()
    {
        $startYear = $this->getYear();
        $endYear = $this->getYear() + 1;
        if ($this->getMonth() < 7) {
            $startYear = $this->getYear() - 1;
            $endYear = $this->getYear();
        }
        $start = self::create(mktime(0, 0, 0, 7, 1, $startYear));
        $end = self::create(mktime(23, 59, 59, 6, 30, $endYear));
        return array($start, $end);
    }




    /**
     * Get the UTC version of the date, I think this works needs more testing.....
     *
     * @return Date
     */
    public function getUTCDate()
    {
        $UTC = new \DateTimeZone("UTC");
        return new self($this->getTimestamp(), $UTC);
    }

    /**
     * Set the time of a date object to 23:59:59
     *
     * @return Date
     */
    public function ceil()
    {
        $ts = mktime(23, 59, 59, $this->getMonth(), $this->getDate(), $this->getYear());
        $d = self::create($ts);
        return $d;
    }

    /**
     * Set the time of a date object to 00:00:00
     *
     * @return Date
     */
    public function floor()
    {
        $ts = mktime(0, 0, 0, $this->getMonth(), $this->getDate(), $this->getYear());
        $d = self::create($ts);
        return $d;
    }

    /**
     * Get the first day of this dates month
     *
     * @return Date
     */
    public function getMonthStart()
    {
        $ts = mktime(0, 0, 0, $this->getMonth(), 1, $this->getYear());
        return self::create($ts);
    }

    /**
     * Get the last day of this dates month
     *
     * @return Date
     */
    public function getMonthEnd()
    {
        $lastDay = self::getMonthDays($this->getMonth(), $this->getYear());
        $ts = mktime(23, 59, 59, $this->getMonth(), $lastDay, $this->getYear());
        return self::create($ts);
    }

    /**
     * Get the first day of this dates week
     * $fdow = First Day Of The Week - Default 0 = sunday
     *  o 0 - Sunday
     *  o 1 - Monday
     *  o 2 - Tuesday
     *  o etc
     *
     * @param int $fdow (optional)
     * @return Date
     */
    public function getWeekStart($fdow = '0')
    {
        throw new FatalException('Needs to be implemented');
    }

    /**
     * Get the last day of this dates week
     * $fdow = First Day Of The Week - Default 0 = sunday
     *  o 0 - Sunday
     *  o 1 - Monday
     *  o 2 - Tuesday
     *  o etc
     *
     * @param int $fdow (optional)
     * @return Date
     */
    public function getWeekEnd($fdow = '0')
    {
        throw new FatalException('Needs to be implemented');
    }



    /**
     * Add seconds to a date.
     *
     * @param int $sec
     * @return Date
     */
    public function addSeconds($sec)
    {
        return self::create($this->getTimestamp() + $sec, $this->getTimezone());
    }

    /**
     * Adds days to date and returns a new instance.
     * NOTE: Days are calculated as ($days * 86400)
     *
     * To subtract days, use a negative number of days.
     * @param int $days
     * @return Date
     */
    public function addDays($days)
    {
        return self::create($this->getTimestamp() + ($days * 86400));
    }

    /**
     * Add actual months to a date
     * This method tryes to correct for end month days
     * For example adding one month to Feb 28 would return Mar 31
     *
     * @param int $months
     * @return Date
     */
    public function addMonths($months)
    {
        $ts = mktime($this->getHour(), $this->getMinute(), $this->getSecond(), $this->getMonth() + $months, 1, $this->getYear());
        $tmpDate = self::create($ts);
        if ($this->getDate() == self::getMonthDays($this->getMonth(), $this->getYear()) || $this->getDate() > $tmpDate->getMonthEnd()->getDate()) {
            $ts = mktime($this->getHour(), $this->getMinute(), $this->getSecond(), $this->getMonth() + $months, $tmpDate->getMonthEnd()->getDate(), $this->getYear());
        } else {
            $ts = mktime($this->getHour(), $this->getMinute(), $this->getSecond(), $this->getMonth() + $months, $this->getDate(), $this->getYear());
        }
        $date = self::create($ts);
        return $date;
    }

    /**
     * Add actual years to a date
     *
     * @param int $years
     * @return Date
     */
    public function addYears($years)
    {
        $ts = mktime($this->getHour(), $this->getMinute(), $this->getSecond(), $this->getMonth(), $this->getDate(), $this->getYear() + $years);
        return self::create($ts);
    }




    /**
     * Returns the difference between this date and other in days.
     *
     * @param Date $other
     * @return int
     */
    public function dayDiff(Date $other)
    {
        return ceil(($this->getTimestamp() - $other->getTimestamp()) / 86400);
    }

    /**
     * Return the diffrence between this date and other in hours.
     *
     * @param Date $other
     * @return int
     */
    public function hourDiff(Date $other)
    {
        return ceil(($this->getTimestamp() - $other->getTimestamp()) / 3600);
    }



    /**
     * Compares the value to another instance of date.
     *
     * @param Date $other
     * @return int Returns -1 if less than , 0 if equal to, 1 if greater than.
     */
    function compareTo(Date $other)
    {
        $retVal = 1;
        if ($this->getTimestamp() < $other->getTimestamp()) {
            $retVal = -1;
        } elseif ($this->getTimestamp() == $other->getTimestamp()) {
            $retVal = 0;
        }

        return $retVal;
    }

    /**
     * Checks if the date value is greater than the value of another instance of date.
     *
     * @param Date
     * @return bool
     */
    function greaterThan(Date $other)
    {
        return ($this->compareTo($other) > 0);
    }
    /**
     * Checks if the date value is greater than or equal the value of another instance of date.
     *
     * @param Date
     * @return bool
     */
    function greaterThanEqual(Date $other)
    {
        return ($this->compareTo($other) >= 0);
    }

    /**
     * Checks if the date value is less than the value of another instance of date.
     *
     * @param Date
     * @return bool
     */
    function lessThan(Date $other)
    {
        return ($this->compareTo($other) < 0);
    }

    /**
     * Checks if the date value is less than or equal the value of another instance of date.
     *
     * @param Date
     * @return bool
     */
    function lessThanEqual(Date $other)
    {
        return ($this->compareTo($other) <= 0);
    }

    /**
     * Checks if the date is equal to the value of another instance of date.
     *
     * @param Date
     * @return bool
     */
    function equals(Date $other)
    {
        return ($this->compareTo($other) == 0);
    }




    /**
     * Get the integer value for the hour
     *
     * @return int
     */
    public function getHour()
    {
        return intval($this->toString('H'), 10);
    }

    /**
     * Get the integer value of teh minute
     *
     * @return int
     */
    public function getMinute()
    {
        return intval($this->toString('i'), 10);
    }

    /**
     * Get the seconds integer value
     *
     * @return int
     */
    public function getSecond()
    {
        return intval($this->toString('s'), 10);
    }

    /**
     * Get the integer value of the day date.
     * Day of the month without leading zeros 	1 to 31
     *
     * @return int
     */
    public function getDate()
    {
        return intval($this->toString('j'), 10);
    }

    /**
     * Get the integer value of the month
     * Numeric representation of a month, without leading zeros 	1 through 12
     *
     * @return int
     */
    public function getMonth()
    {
        return intval($this->toString('n'), 10);
    }

    /**
     * Get the 4 digit integer value of the year
     * A full numeric representation of a year, 4 digits 	Examples: 1999 or 2003
     *
     * @return int
     */
    public function getYear()
    {
        return intval($this->toString('Y'), 10);
    }



    /**
     * Return a string representation of this object
     *
     * @param string $format  Optional date format string
     * @return string
     */
    public function toString($format = 'Y-m-d H:i:s')
    {
        return $this->format($format);
    }

    /*
     * convert a date into a string that tells how long
     * ago that date was.... eg: 2 days ago, 3 minutes ago.
     *
     * @param Date $dte (optional)
     * @return string
     */
    function toRelativeString($dte = null)
    {
        if (!$dte) {
            $dte = $this;
        }
        $c = getdate();
        $p = array('year', 'mon', 'mday', 'hours', 'minutes', 'seconds');
        $display = array('year', 'month', 'day', 'hour', 'minute', 'second');
        $factor = array(0, 12, 30, 24, 60, 60);
        preg_match("/([0-9]{4})(\\-)([0-9]{2})(\\-)([0-9]{2}) ([0-9]{2})(\\:)([0-9]{2})(\\:)([0-9]{2})/", $dte->toString(), $matches);
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
