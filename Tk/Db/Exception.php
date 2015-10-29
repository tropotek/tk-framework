<?php
namespace Tk\Db;

/**
 * Class Exception
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Exception extends \Tk\Exception
{


    /**
     * Set any memory, code dump data to display in the eception error
     *
     * @param string $dump
     */
    public function setDump($dump)
    {
        $dump = explode("\n", str_replace(array(',', ' WHERE', ' FROM', ' LIMIT', ' ORDER', ' LEFT JOIN'), array(', ', "\n  WHERE", "\n  FROM", "\n  LIMIT", "\n  ORDER", "\n  LEFT JOIN"),$dump));
        foreach ($dump as $i => $s) {
            $dump[$i] = wordwrap($s, 120, "\n    ");
        }
        $dump = implode("\n", $dump);
        return parent::setDump('  '.$dump);
    }


}
