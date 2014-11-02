<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Str;

/**
 * This is an object for creating a <ul></ul> string
 *
 * 
 * @package Tk\Str
 */
class Ul extends Iface
{

    protected $ordered = false;

    protected $list = array();


    /**
     * Create a Ul object with a list of items
     *
     * @param array $list
     * @param bool $ordered Use ol instead of ul
     * @return Ul
     */
    static function create($list, $ordered = false)
    {
        $obj = new self();
        $obj->list = $list;
        $obj->ordered = $ordered;
        return $obj;
    }

    public function toString()
    {
        $str = "<ul>\n";
        if ($this->ordered) {
            $str = "<ol>\n";
        }
        foreach ($this->list as $k => $v) {
            if (is_string($k)) {
                $v = '<strong>'.$k.':</strong> ' . $v;
            }
            $str .= sprintf('%s<li>%s</li>%s', self::TAB, $v, "\n");
        }
        if (!$this->ordered) {
            $str .= "</ul>\n";
        } else {
            $str .= "</ol>\n";
        }
        return $str;
    }

}
