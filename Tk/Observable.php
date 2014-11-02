<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk;

/**
 * This object is the base for all objects
 *
 * @package Tk
 */
interface Observable
{

    /**
     * Notify all observers of the uncaught Exception
     * so they can handle it as needed.
     *
     * @param string $name          (Optional) The name of the event to notify/fire
     * @return \Tk\Observable
     */
    public function notify($name = '');


    /**
     * Attaches an SplObserver to
     * the ExceptionHandler to be notified
     * when an uncaught Exception is thrown.
     *
     * @param \Tk\Observer $obs      The observer to attach
     * @param string $name          (Optional) The event name to attache the observer to
     * @param int $idx          (Optional) The position to insert the observer into
     * @return string               The observer uniqueID
     */
    public function attach(Observer $obs, $name = '', $idx = null);


    /**
     * Detaches the SplObserver object from the stack
     *
     * @param \Tk\Observer        The observer to detach
     * @return \Tk\Observable
     */
    public function detach(Observer $obs);


}

/**
 * This object is the base for all objects
 *
 * @package Tk
 */
class ObservableSlave implements Observable
{

    /**
     * An array of SplObserver objects to notify
     *
     * @var array
     */
    private $observers = array();

    /**
     * @var \Tk\Object
     */
    private $parent = null;


    /**
     * __construct
     *
     * @param \Tk\Object $parent
     */
    public function __construct($parent)
    {
        $this->parent = $parent;
    }


    /**
     * Get the observer list
     *
     * @return array
     */
    public function getObserverList()
    {
        return $this->observers;
    }

    /**
     * Return a list of current events fo this object.
     *
     * @return array
     */
    public function getObserverNames()
    {
        return array_keys($this->getObserverList());
    }

    /**
     * Set the observer for this object.
     * This should only be used when the array follows the observer list structure
     * array(
     *   'event1' => array($observerObj1, $observerObj2),
     *   etc...
     * );
     *
     * @param array $arr
     */
    public function setObserverList($arr)
    {
        $this->observers = $arr;
    }

    /**
     * Notify all observers of the uncaught Exception
     * so they can handle it as needed.
     *
     * @param string $name          (Optional) The name of the event to notify/fire
     * @return \Tk\Observable
     */
    public function notify($name = '')
    {
        if (isset($this->observers[$name])) {
            foreach ($this->observers[$name] as $obs) {
                $obs->update($this->parent);
                // Save mem where possible.
                gc_collect_cycles();
            }
        }
        return $this;
    }


    /**
     * Attaches an SplObserver to
     * the ExceptionHandler to be notified
     * when an uncaught Exception is thrown.
     *
     * @param \Tk\Observer $obs    The observer to attach
     * @param string $name         (optional) The event name to attache the observer to
     * @param int $idx             (optional) The position to insert the observer into
     * @return \Tk\Observable
     */
    public function attach(Observer $obs, $name = '', $idx = null)
    {
        if (!isset($this->observers[$name])) {
            $this->observers[$name] = array();
        }
        $id = spl_object_hash($obs);
        if ($idx !== null && $idx >= 0 && $idx < count($this->observers[$name])) {
            array_splice($this->observers[$name], $idx, 0, array($id => $obs));
        } else {
            $this->observers[$name][$id] = $obs;
        }
        return $this;
    }

    /**
     * Detaches the SplObserver object from the stack
     *
     * @param \Tk\Observer        The observer to detach
     * @return \Tk\Observable
     */
    public function detach(Observer $obs)
    {
        $id = spl_object_hash($obs);
        foreach ($this->observers as $name => $arr1) {
            foreach ($arr1 as $oid => $o) {
                if ($id == $oid) {
                    unset($this->observers[$name][$id]);
                    return $this;
                }
            }
        }
        return $this;
    }

}
