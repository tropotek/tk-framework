<?php
namespace Tk;


/**
 *
 *
 * @author Tropotek <http://www.tropotek.com/>
 * @see http://git.snooey.net/Mirrors/php-slim/
 */
class Collection implements \ArrayAccess, \IteratorAggregate, \Countable
{

    private array $_data = [];


    public function __construct(array $items = [])
    {
        foreach ($items as $key => $value) {
            $this->set($key, $value);
        }
    }


    /**
     * Return items in the $src array where the keys match the $regex supplied
     */
    public static function findByRegex(array $src, string $regex): array
    {
        $a = [];
        foreach ($src as $key => $value) {
            if (!preg_match($regex, $key)) continue;
            $a[$key] = $value;
        }
        return $a;
    }

    /**
     * Return items from $src where the key is in the array $keys
     */
    public static function findIntersects(array $src, array $keys): array
    {
        return array_intersect_key($src, array_fill_keys($keys, null));
    }

    /**
     * prefix a string to all array keys
     *
     * @todo Delete if not used!!!
     */
    public static function prefixArrayKeys(array $array, string $prefix): array
    {
        if ($prefix != '') {
            foreach ($array as $k => $v) {
                $array[$prefix . $k] = $v;
                unset($array[$k]);
            }
        }
        return $array;
    }

    /**
     * flatten a multidimensional array to a single-dimensional array
     * @note All key values will be lost.
     */
    public static function arrayFlatten(array $array): array
    {
        $return = [];
        //array_walk_recursive($array, function($a) use (&$return) { if ($a !== null) $return[] = $a; });
        array_walk_recursive($array, function($a) use (&$return) { $return[] = $a;});
        return $return;
    }

    /**
     * Return the difference of 2 multidimensional arrays
     * If no difference an empty array is returned.
     *
     * @site http://php.net/manual/en/function.array-diff-assoc.php
     * @author telefoontoestel at hotmail dot com
     */
    public static function arrayDiffRecursive(array $array1, array $array2): array
    {
        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (!isset($array2[$key])) {
                    $difference[$key] = $value;
                } elseif (!is_array($array2[$key])) {
                    $difference[$key] = $value;
                } else {
                    $new_diff = self::arrayDiffRecursive($value, $array2[$key]);
                    if ($new_diff) $difference[$key] = $new_diff;
                }
            } elseif (!array_key_exists($key, $array2) || $array2[$key] != $value) {
                $difference[$key] = $value;
            }
        }
        return !isset($difference) ? [] : $difference;
    }

    /**
     * Return a readable string representation of this object
     */
    public static function arrayToString(array $arr): string
    {
        $str = "";
        foreach ($arr as $k => $v) {
            if (is_object($v)) {
                $str .= "[$k] => {" . get_class($v) . "}\n";
            } elseif (is_array($v)) {
                $str .= "[$k] =>  array[" . count($v) . "]\n";
            } else {
                $str .= "[$k] => $v\n";
            }
        }
        return $str;
    }


    /**
     * Add a list of items to the collection
     *
     * @param array $items Key-value array of data to append to this collection
     */
    public function replace(array $items): Collection
    {
        foreach ($items as $key => $value) {
            $this->set($key, $value);
        }
        return $this;
    }

    /**
     * Set an item in the collection
     */
    public function set(string $key, mixed $value): Collection
    {
        $this->_data[$key] = $value;
        return $this;
    }

    public function prepend(string $key, mixed $value, ?string $refKey = null): mixed
    {
        if (!$this->has($refKey)) {
            $this->_data = array_merge([$key => $value], $this->_data);
        } else {
            $a = [];
            foreach ($this->_data as $k => $v) {
                if ($k === $refKey) $a[$key] = $value;
                $a[$k] = $v;
            }
            $this->_data = $a;
        }
        return $value;
    }

    public function append(string $key, mixed $value, ?string $refKey = null): mixed
    {
        if (!$this->has($refKey)) {
            $this->set($key, $value);
        } else {
            $a = [];
            foreach ($this->_data as $k => $v) {
                $a[$k] = $v;
                if ($k === $refKey) $a[$key] = $value;
            }
            $this->_data = $a;
        }
        return $value;
    }

    /**
     * Get collection item for key
     *
     * @param mixed|null $default Return value if the key does not exist
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all($key) ?: $default;
    }

    /**
     * Get all items in collection
     *
     * @param string|null $key The name of the headers to return or null to get them all
     */
    public function all(?string $key = null): mixed
    {
        if ($key !== null) {
            return $this->_data[$key] ?? [];
        }
        return $this->_data;
    }

    /**
     * Get collection array keys
     */
    public function keys(): array
    {
        return array_keys($this->_data);
    }

    /**
     * Does this collection have a given key?
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->_data);
    }

    /**
     * Remove item from collection
     */
    public function remove(string $key): Collection
    {
        unset($this->_data[$key]);
        return $this;
    }


    /**
     * Does this collection have a given key?
     *
     * @param  string $key The data key
     *
     * @interface \ArrayAccess
     */
    public function offsetExists($key): bool
    {
        return $this->has($key);
    }

    /**
     * Get collection item for key
     *
     * @param string $key The data key
     *
     * @return mixed The key's value, or the default value
     * @interface \ArrayAccess
     */
    public function offsetGet($key): mixed
    {
        return $this->get($key);
    }

    /**
     * Set collection item
     *
     * @param string $key The data key
     * @param mixed $value The data value
     * @interface \ArrayAccess
     */
    public function offsetSet($key, $value): void
    {
        $this->set($key, $value);
    }

    /**
     * Remove item from collection
     *
     * @param string $key The data key
     * @interface \ArrayAccess
     */
    public function offsetUnset($key): void
    {
        $this->remove($key);
    }

    /**
     * Get number of items in collection
     *
     * @return int
     * @interface Countable
     */
    public function count(): int
    {
        return count($this->_data);
    }

    /**
     * Get collection iterator
     *
     * @return \ArrayIterator
     * @interface IteratorAggregate
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->_data);
    }

}