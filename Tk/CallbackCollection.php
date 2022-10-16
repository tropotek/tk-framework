<?php
namespace Tk;

/**
 * Use this to store and execute an array of callback events for objects
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class CallbackCollection
{
    const DEFAULT_PRIORITY = 10;

    /**
     * A multidimensional array of callable functions
     * array[$priority][] = callable;
     */
    private array $callbackList = [];

    private bool $enabled = true;


    public static function create(): CallbackCollection
    {
        return new self();
    }

    /**
     * Callback:
     *   o function (\Dom\Template $fieldGroup, \Tk\Form\Renderer\FieldGroup $element) { }
     *   o ['MyClass', 'myCallbackMethod']
     *   o [$obj, 'myCallbackMethod']
     *
     * @see https://www.php.net/manual/en/language.types.callable.php
     */
    public function append(callable $callable, int $priority=self::DEFAULT_PRIORITY): CallbackCollection
    {
        $this->callbackList[$priority][] = $callable;
        return $this;
    }

    /**
     * EG:
     *    closure: function (\Dom\Template $fieldGroup, \Tk\Form\Renderer\FieldGroup $element) { }
     *    string: '\Tk\Db\Model::method'
     *
     * @param callable|null $callable
     * @param int $priority
     * @return $this
     */
    public function prepend(callable $callable, int $priority = self::DEFAULT_PRIORITY): CallbackCollection
    {
        if (!$callable) return $this;
        if (!isset($this->callbackList[$priority]))
            $this->callbackList[$priority] = [];
        $this->callbackList[$priority] = [$callable] + $this->callbackList[$priority];
        return $this;
    }

    /**
     * Clear a priority queue or an item in the queue
     */
    public function remove(int $priority = self::DEFAULT_PRIORITY, ?int $index = null): CallbackCollection
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
     * Remove a callable if you have the callback handle available
     */
    public function removeCallable(callable $callable): CallbackCollection
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
     * @return mixed|bool|null Returns false if the Callback is disabled
     */
    public function execute(...$args)
    {
        if (!$this->isEnabled()) return false;
        $this->orderList();
        $return = null;
        foreach ($this->callbackList as $priority => $list) {
            foreach ($list as $i => $callable) {
                $args[] = $return;
                $r = call_user_func_array($callable, $args);
                if ($r !== null) $return = $r;
            }
        }
        return $return;
    }

    /**
     * Reset the CallbackCollection queue
     */
    public function reset(): CallbackCollection
    {
        $this->callbackList = [];
        return $this;
    }

    /**
     * Return true if any callbacks are registered
     */
    public function isCallable(): bool
    {
        foreach ($this->callbackList as $priority => $list) {
            foreach ($list as $i => $callable) {
                if (is_callable($callable)) return true;
            }
        }
        return false;
    }


    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     *
     */
    public function setEnabled(bool $enabled): CallbackCollection
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * order the list with priorities as they should be
     */
    protected function orderList(): bool
    {
        return ksort($this->callbackList, \SORT_REGULAR);
    }


}