<?php
namespace Tk;

/**
 * Use this to store and execute an array of callback events for objects
 *
 *
 * @author Tropotek <info@tropotek.com>
 * @created: 2/08/18
 * @link http://www.tropotek.com/
 * @license Copyright 2018 Tropotek
 */
class Callback
{
    const DEFAULT_PRIORITY = 10;

    /**
     * A multidimensional array of callable functions
     *
     * array[$priority][] = callable;
     *
     * @var array
     */
    private $callbackList = array();

    /**
     * @var bool
     */
    private $enabled = true;


    /**
     * @return Callback
     */
    public static function create()
    {
        return new self();
    }

    /**
     * Callback: function (\Dom\Template $fieldGroup, \Tk\Form\Renderer\FieldGroup $element) { }
     *
     * @param callable|null $callable
     * @param int $priority
     * @return $this
     */
    public function append(?callable $callable, int $priority=self::DEFAULT_PRIORITY)
    {
        if (!$callable) return $this;
        if (!isset($this->callbackList[$priority]))
            $this->callbackList[$priority] = array();
        $this->callbackList[$priority][] = $callable;
        return $this;
    }

    /**
     * Callback: function (\Dom\Template $fieldGroup, \Tk\Form\Renderer\FieldGroup $element) { }
     *
     * @param callable|null $callable
     * @param int $priority
     * @return $this
     */
    public function prepend(?callable $callable, int $priority=self::DEFAULT_PRIORITY)
    {
        if (!$callable) return $this;
        if (!isset($this->callbackList[$priority]))
            $this->callbackList[$priority] = array();
        $this->callbackList[$priority] = array_merge(array($callable), $this->callbackList[$priority]);
        return $this;
    }

    /**
     * @param null|int $priority
     * @return $this
     */
    public function reset($priority = null)
    {
        if (is_numeric($priority)) {
            if (isset($this->callbackList[$priority]))
                $this->callbackList[$priority] = array();
        } else {
            $this->callbackList = array();
        }
        return $this;
    }

    /**
     * Alias for reset()
     *
     * @param null $priority
     * @return callable
     */
    public function clear($priority = null)
    {
        return $this->reset($priority);
    }

    /**
     * @param int $priority
     * @param null|int $index
     * @return $this
     */
    public function remove(int $priority, $index = null)
    {
        if ($index === null) {
            if (isset($this->callbackList[$priority]))
                unset($this->callbackList[$priority]);
        } else {
            if (isset($this->callbackList[$priority][$index]))
                unset($this->callbackList[$priority][$index]);
        }
        return $this;
    }

    /**
     * @param callable $callable
     * @return $this
     */
    public function removeCallable($callable)
    {
        foreach ($this->callbackList as $priority => $list) {
            foreach ($list as $i => $c2) {
                if ($c2 === $callable) {
                    $this->remove($priority, $i);
                }
            }
        }
        return $this;
    }

    /**
     * @return mixed|null Returns false if the Callback object is disabled
     */
    public function execute()
    {
        if (!$this->isEnabled()) return false;
        $this->orderList();
        $return = null;
        foreach ($this->callbackList as $priority => $list) {
            foreach ($list as $i => $callable) {
                $args = func_get_args();
                $args[] = $return;
                $r = call_user_func_array($callable, $args);
                if ($r !== null) $return = $r;
            }
        }
        return $return;
    }

    /**
     * Return true if any callbacks are registered
     *
     * @return bool
     */
    public function isCallable()
    {
        foreach ($this->callbackList as $priority => $list) {
            foreach ($list as $i => $callable) {
                if (is_callable($callable)) return true;
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     * @return Callback
     */
    public function setEnabled(bool $enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * order the list with priorities as they should be
     * @return bool
     */
    protected function orderList()
    {
        return ksort($this->callbackList, \SORT_REGULAR);
    }


}