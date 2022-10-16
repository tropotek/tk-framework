<?php
namespace Tk;


/**
 * @author Tropotek <http://www.tropotek.com/>
 */
class Math
{

    public static function median(array $arr): float
    {
        sort($arr);
        $count = count($arr); //total numbers in array
        if ($count && $count < 2) return $arr[0];
        if (!$count) return 0;
        $middleval = floor(($count-1)/2); // find the middle value, or the lowest middle value
        if($count % 2) { // odd number, middle is the median
            $median = $arr[$middleval];
        } else { // even number, calculate avg of 2 medians
            $low = $arr[$middleval];
            $high = $arr[$middleval+1];
            $median = (($low+$high)/2);
        }
        return $median;
    }

    public static function average(array $arr): float
    {
        if (!count($arr)) return 0;
        return array_sum($arr)/count($arr);
    }

}