<?php
namespace Tk;

class Date
{

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
     * Use this to format form dates, change it in the script bootstrap if required
     */
    public static string $FORM_FORMAT = 'd/m/Y';

    /**
     * Month end days.
     */
    private static array $monthEnd = ['1' => '31', '2' => '28', '3' => '31',
        '4' => '30', '5' => '31', '6' => '30', '7' => '31', '8' => '31',
        '9' => '30', '10' => '31', '11' => '30', '12' => '31'];


    /**
     * __construct
     * no instances to be created
     */
    private function __construct() { }


    /**
     * Create a DateTime object with system timezone
     *
     * @param null|\DateTimeZone|string $timezone
     */
    public static function create(string $time = 'now', $timezone = null): \DateTime
    {
        try {
            if (is_string($timezone)) {
                $timezone = new \DateTimeZone($timezone);
            }
            if (!$timezone) {
                $timezone = new \DateTimeZone(date_default_timezone_get());
            }

            if (preg_match('/^[0-9]{1,11}$/', $time)) {
                $time = '@'.$time;
            }

            $date = new \DateTime($time, $timezone);
            $date->setTimezone($timezone);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return $date ?? new \DateTime();
    }

    /**
     * Create a date from a string returned from a form field
     *
     * @param null|\DateTimeZone|string $timezone
     * @throws \Exception
     */
    public static function createFormDate(string $dateStr, $timezone = null, string $format = ''): \DateTime
    {
        try {
            if ($timezone && is_string($timezone)) {
                $timezone = new \DateTimeZone($timezone);
            }
            if (!$timezone) {
                $timezone = new \DateTimeZone(date_default_timezone_get());
            }
            if (!$format) {
                $format = self::$FORM_FORMAT;
            }
            $date = \DateTime::createFromFormat($format, $dateStr);
            if (!$date && str_ends_with($dateStr, 'Z')) {   // could be ISO format of: 2020-11-23T01:20:27.164Z
                $date = new \DateTime($dateStr);
            }
            if ($date) {
                $date->setTimezone($timezone);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return $date ?? new \DateTime();
    }


    /**
     * Get the months ending date 1 = 31-Jan, 12 = 31-Dec
     */
    public static function getMonthDays(int $m, int $y = 0): int
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
     */
    public static function isLeapYear(int $y): int
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
     * Use this function to find the next date
     * TODO: Add more description and examples
     *
     * @param string $frequency ['weekly', 'fortnightly', 'monthly']
     * @throws \Exception
     */
    public static function getPeriodDates(string $frequency, \DateTime $dateStart, ?\DateTime $dateEnd = null): array
    {
        //$start_date is string e.g 06-23-2016
        //$frequency is also string e.g weekly, fortnightly, monthly
        //$end_date is optional string: limit to the dates to be generated. Default = today

        $dates = [];
        $dt = $dateStart;
        $dtUntil = $dateEnd;
        if (!$dateEnd)
            $dtUntil = self::floor();

        // conversion table: frequency to date modifier string
        $modifiers = [
            "weekly" => "+1 week",
            "fortnightly" => "+2 weeks",
            "monthly" => "+1 month"
        ];
        $modifier = $modifiers[$frequency];
        $dt->modify($modifier);
        while(self::floor($dt) <= self::floor($dtUntil)) {
            $dates[] = Date::create($dt->getTimestamp());
            $dt->modify($modifier);
        }
        return $dates; //array returned
    }

    /**
     * Get the financial year of this date
     * list($start, $end) = Tk\Date::getFinancialYear($date);
     *
     * @return \DateTime[]|array
     * @throws \Exception
     */
    public static function getFinancialYear(\DateTime $date = null): array
    {
        if (!$date) $date = self::create();
        $year = (int)$date->format('Y');
        $month = (int)$date->format('n');
        if ($month < 7) {
            $year--;
        }
        $start = new \DateTime($year.'-07-01 00:00:00', $date->getTimezone());
        $end = new \DateTime(($year+1).'-06-30 23:59:59', $date->getTimezone());

        return [$start, $end];
    }


    /**
     * Set the time of a date object to 23:59:59
     * @throws \Exception
     */
    public static function ceil(?\DateTime $date = null): \DateTime
    {
        if (!$date) $date = self::create();
        return new \DateTime($date->format('Y-m-d 23:59:59'), $date->getTimezone());
    }

    /**
     * Set the time of a date object to 00:00:00
     * @throws \Exception
     */
    public static function floor(?\DateTime $date = null): \DateTime
    {
        if (!$date) $date = self::create();
        return new \DateTime($date->format('Y-m-d 00:00:00'), $date->getTimezone());
    }

    /**
     * Get the first day of this dates month
     * @throws \Exception
     */
    public static function getMonthStart(?\DateTime $date = null): \DateTime
    {
        if (!$date) $date = self::create();
        return new \DateTime($date->format('Y-m-01 00:00:00'), $date->getTimezone());
    }

    /**
     * Get the last day of this dates month
     * @throws \Exception
     */
    public static function getMonthEnd(?\DateTime $date = null): \DateTime
    {
        if (!$date) $date = self::create();
        $lastDay = self::getMonthDays($date->format('n'), $date->format('Y'));
        return new \DateTime($date->format('Y-m-'.$lastDay.' 23:59:59'), $date->getTimezone());
    }


    /**
     * Get the first day of this dates month
     * @throws \Exception
     */
    public static function getYearStart(\DateTime $date = null): \DateTime
    {
        if (!$date) $date = self::create();
        return new \DateTime($date->format('Y-01-01 00:00:00'), $date->getTimezone());
    }

    /**
     * Get the last day of this dates month
     *
     * @param \DateTime $date
     * @return \DateTime
     * @throws \Exception
     */
    public static function getYearEnd(\DateTime $date = null)
    {
        if (!$date) $date = self::create();
        return new \DateTime($date->format('Y-12-31 23:59:59'), $date->getTimezone());
    }


    /**
     * Returns the difference between this date and other in days.
     */
    public static function dayDiff(\DateTime $from, \DateTime $to): int
    {
        return ceil(($from->getTimestamp() - $to->getTimestamp()) / self::DAY);
    }

    /**
     * Return the difference between this date and other in hours.
     */
    public static function hourDiff(\DateTime $from, \DateTime $to): int
    {
        return ceil(($from->getTimestamp() - $to->getTimestamp()) / self::HOUR);
    }

    /**
     * Compares the value to another instance of date.
     * @return int Returns -1 if less than , 0 if equal to, 1 if greater than.
     */
    public static function compareTo(\DateTime $from, \DateTime $to): int
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
     */
    public static function greaterThan(\DateTime $from, \DateTime $to): bool
    {
        return (self::compareTo($from, $to) > 0);
    }

    /**
     * Checks if the date value is greater than or equal the value of another instance of date.
     */
    public static function greaterThanEqual(\DateTime $from, \DateTime $to): bool
    {
        return (self::compareTo($from, $to) >= 0);
    }

    /**
     * Checks if the date value is less than the value of another instance of date.
     */
    public static function lessThan(\DateTime $from, \DateTime $to): bool
    {
        return (self::compareTo($from, $to) < 0);
    }

    /**
     * Checks if the date value is less than or equal the value of another instance of date.
     */
    public static function lessThanEqual(\DateTime $from, \DateTime $to): bool
    {
        return (self::compareTo($from, $to) <= 0);
    }

    /**
     * Checks if the date is equal to the value of another instance of date.
     */
    public static function equals(\DateTime $from, \DateTime $to): bool
    {
        return (self::compareTo($from, $to) == 0);
    }

    /**
     * convert a date into a string that tells how long
     * ago that date was.... eg: 2 days ago, 3 minutes ago.
     *
     * Note: Only works for dates in the past...
     * @throws Exception
     */
    public static function toRelativeString(\DateTime $date = null): string
    {

        if ($date > new \DateTime()) throw new Exception('Date must be in the past.');

        $c = getdate();
        $p = ['year', 'mon', 'mday', 'hours', 'minutes', 'seconds'];
        $display = ['year', 'month', 'day', 'hour', 'minute', 'second'];
        $factor = [0, 12, 30, 24, 60, 60];
        preg_match("/([0-9]{4})(\\-)([0-9]{2})(\\-)([0-9]{2}) ([0-9]{2})(\\:)([0-9]{2})(\\:)([0-9]{2})/", $date->format(self::FORMAT_ISO_DATETIME), $matches);
        $d = [
            'seconds' => $matches[10],
            'minutes' => $matches[8],
            'hours' => $matches[6],
            'mday' => $matches[5],
            'mon' => $matches[3],
            'year' => $matches[1],
        ];

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
