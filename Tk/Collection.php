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

    private array $data = [];


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
        if ($prefix != '' && is_string($prefix)) {
            foreach ($array as $k => $v) {
                $array[$prefix . $k] = $v;
                unset($array[$k]);
            }
        }
        return $array;
    }


    /**
     * flatten a multidimensional array to a single-dimensional array
     *
     * @note All key values will be lost.
     * @todo Delete if not used!!!
     */
    public static function arrayFlatten(array $array): array
    {
        $return = [];
        array_walk_recursive($array, function($a) use (&$return) { if ($a !== null) $return[] = $a; });
        return $return;
    }

    /**
     * Return the difference of 2 multidimensional arrays
     * If no difference null is returned.
     *
     * @return null|array   Returns null if there are no differences
     * @site http://php.net/manual/en/function.array-diff-assoc.php
     * @author telefoontoestel at hotmail dot com
     * @todo Delete if not used!!!
     */
    public static function arrayDiffRecursive(array $array1, array $array2)
    {
        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (!isset($array2[$key])) {
                    $difference[$key] = $value;
                } elseif (!is_array($array2[$key])) {
                    $difference[$key] = $value;
                } else {
                    $new_diff = self::arrayDiffRecursive($value, $array2[$key]);
                    if ($new_diff != false) {
                        $difference[$key] = $new_diff;
                    }
                }
            } elseif (!array_key_exists($key, $array2) || $array2[$key] != $value) {
                $difference[$key] = $value;
            }
        }
        return !isset($difference) ? null : $difference;
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
     * @return $this
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
     *
     * @param mixed $value
     */
    public function set(string $key, $value): Collection
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * Get collection item for key
     *
     * @param mixed $default Return value if the key does not exist
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->all($key) ?: $default;
    }

    /**
     * Get all items in collection
     *
     * @param string|null $key The name of the headers to return or null to get them all
     *
     * @return mixed
     */
    public function all(?string $key = null)
    {
        if ($key !== null) {
            return $this->data[$key] ?? [];
        }
        return $this->data;
    }

    /**
     * Get collection array keys
     */
    public function keys(): array
    {
        return array_keys($this->data);
    }

    /**
     * Does this collection have a given key?
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Remove item from collection
     */
    public function remove(string $key): Collection
    {
        unset($this->data[$key]);
        return $this;
    }

    /**
     * Remove all items from collection
     */
    public function clear(): Collection
    {
        $this->data = [];
        return $this;
    }



    /**
     * Does this collection have a given key?
     *
     * @param  string $key The data key
     *
     * @return bool
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
        return count($this->data);
    }

    /**
     * Get collection iterator
     *
     * @return \ArrayIterator
     * @interface IteratorAggregate
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->data);
    }

}