<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Log\Adapter;

/**
 * A log observer interface...
 *
 * @package Tk\Log\Adapter
 */
class File extends Iface
{
    /**
     * @var string
     */
    protected $pathname = '';


    /**
     * constructor
     *
     * @param string $pathname
     * @param int $level
     */
    public function __construct($pathname, $level)
    {
        parent::__construct($level);
        $this->pathname = $pathname;
    }

    /**
     * publish the log message
     *
     * @param \Tk\Log\Log $obj
     */
    public function update($obj)
    {
        if (!($this->getLevel() & $obj->getType()) ) {
            return;
        }
        if (!is_writable($this->pathname)) {
            return;
        }

        $file = fopen($this->pathname, 'a');
        if ($file !== false) {
            fwrite($file, $obj->getheader() . ': ' . $obj->getMessage() . "\n");
            fclose($file);
        }
    }

    /**
     * Get the log pathname
     *
     * @return string
     */
    public function getPathname()
    {
        return $this->pathname;
    }

}