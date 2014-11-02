<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Dispatcher;

/**
 * Ajax Dispatcher
 *
 * This dispatch is designed to catch urls with
 * a /ajax/{Class_Name} at the start then create an instance
 * of the class and execute it using the
 * NOTE: Class names are fully namespaced replacing \ with _
 *
 *
 *
 * @package Tk\Dispatcher
 */
class Ajax extends \Tk\Object implements Iface
{

    /**
     * Use this method to create and return the module class name
     *
     * @param \Tk\Dispatcher\Dispatcher $obs
     */
    public function update($obs)
    {
        if ($obs->getClass()) {
            return;
        }
        $path = $obs->getRequestUrl()->getPath(true);
        if (preg_match('|^/ajax/([0-9a-z_]+)|i', $path, $regs)) {
            $class = \Tk\Config::toNamespace($regs[1]);

            if (class_exists($class) && in_array('Tk\Command\Iface', class_implements($class))) {
                $obs->setClass($class);
                $obs->setOutput('ajax');
                $this->getConfig()->set('res.pageClass', $class);
                $obj = new $class();
                if ($obj instanceof \Tk\Command\Iface) {
                    $this->getConfig()->set('res.page', $obj);
                }
            }




        }
    }
}
