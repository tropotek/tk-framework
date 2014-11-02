<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Model\Map;

/**
 * A column map
 *
 * @package Tk\Model\Map
 */
class DataUrl extends Iface
{
    /**
     *
     * @param string $propertyName
     * @param array $columnName
     * @return \Tk\Model\Map\DataUrl
     */
    static function create($propertyName, $columnNames = array())
    {
        return new self($propertyName, $columnNames);
    }


    /**
     * getPropertyValue
     *
     * @param array $row
     * @return \Tk\Url Or null of not found
     */
    public function getPropertyValue($row)
    {
        $name = current($this->getColumnNames());
        if (isset($row[$name])) {
            return \Tk\Url::createDataUrl($row[$name]);
        }
    }

    /**
     * Get the storage value
     *
     * @param \Tk\Model $obj
     * @return string
     */
    public function getColumnValue($obj)
    {
        $name = $this->getPropertyName();
        $cname = current($this->getColumnNames());
        if ($obj->$name instanceof \Tk\Url) {
            $base = \Tk\Url::createDataUrl('');
            $value = $obj->$name->getPath();
            if (strlen($base->getPath()) > 1) {
                $value = str_replace($base->getPath(), '', $value);
            }
            return array($cname => enquote($value));
        }
        return array($cname => enquote(''));
    }


}
