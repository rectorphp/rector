<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix202407\Nette\Utils;

use RectorPrefix202407\JetBrains\PhpStorm\Language;
use RectorPrefix202407\Nette;
use function is_array, is_int, is_object, count;
/**
 * Array tools library.
 */
class Arrays
{
    use Nette\StaticClass;
    /**
     * Returns item from array. If it does not exist, it throws an exception, unless a default value is set.
     * @template T
     * @param  array<T>  $array
     * @param  array-key|array-key[]  $key
     * @param mixed $default
     * @return ?T
     * @throws Nette\InvalidArgumentException if item does not exist and default value is not provided
     */
    public static function get(array $array, $key, $default = null)
    {
        foreach (is_array($key) ? $key : [$key] as $k) {
            if (is_array($array) && \array_key_exists($k, $array)) {
                $array = $array[$k];
            } else {
                if (\func_num_args() < 3) {
                    throw new Nette\InvalidArgumentException("Missing item '{$k}'.");
                }
                return $default;
            }
        }
        return $array;
    }
    /**
     * Returns reference to array item. If the index does not exist, new one is created with value null.
     * @template T
     * @param  array<T>  $array
     * @param  array-key|array-key[]  $key
     * @return ?T
     * @throws Nette\InvalidArgumentException if traversed item is not an array
     */
    public static function &getRef(array &$array, $key)
    {
        foreach (is_array($key) ? $key : [$key] as $k) {
            if (is_array($array) || $array === null) {
                $array =& $array[$k];
            } else {
                throw new Nette\InvalidArgumentException('Traversed item is not an array.');
            }
        }
        return $array;
    }
    /**
     * Recursively merges two fields. It is useful, for example, for merging tree structures. It behaves as
     * the + operator for array, ie. it adds a key/value pair from the second array to the first one and retains
     * the value from the first array in the case of a key collision.
     * @template T1
     * @template T2
     * @param  array<T1>  $array1
     * @param  array<T2>  $array2
     * @return array<T1|T2>
     */
    public static function mergeTree(array $array1, array $array2) : array
    {
        $res = $array1 + $array2;
        foreach (\array_intersect_key($array1, $array2) as $k => $v) {
            if (is_array($v) && is_array($array2[$k])) {
                $res[$k] = self::mergeTree($v, $array2[$k]);
            }
        }
        return $res;
    }
    /**
     * Returns zero-indexed position of given array key. Returns null if key is not found.
     * @param string|int $key
     */
    public static function getKeyOffset(array $array, $key) : ?int
    {
        return Helpers::falseToNull(\array_search(self::toKey($key), \array_keys($array), \true));
    }
    /**
     * @deprecated  use  getKeyOffset()
     */
    public static function searchKey(array $array, $key) : ?int
    {
        return self::getKeyOffset($array, $key);
    }
    /**
     * Tests an array for the presence of value.
     * @param mixed $value
     */
    public static function contains(array $array, $value) : bool
    {
        return \in_array($value, $array, \true);
    }
    /**
     * Returns the first item (matching the specified predicate if given). If there is no such item, it returns result of invoking $else or null.
     * The $predicate has the signature `function (mixed $value, int|string $key, array $array): bool`.
     * @template T
     * @param  array<T>  $array
     * @return ?T
     */
    public static function first(array $array, ?callable $predicate = null, ?callable $else = null)
    {
        $key = self::firstKey($array, $predicate);
        return $key === null ? $else ? $else() : null : $array[$key];
    }
    /**
     * Returns the last item (matching the specified predicate if given). If there is no such item, it returns result of invoking $else or null.
     * The $predicate has the signature `function (mixed $value, int|string $key, array $array): bool`.
     * @template T
     * @param  array<T>  $array
     * @return ?T
     */
    public static function last(array $array, ?callable $predicate = null, ?callable $else = null)
    {
        $key = self::lastKey($array, $predicate);
        return $key === null ? $else ? $else() : null : $array[$key];
    }
    /**
     * Returns the key of first item (matching the specified predicate if given) or null if there is no such item.
     * The $predicate has the signature `function (mixed $value, int|string $key, array $array): bool`.
     * @return int|string|null
     */
    public static function firstKey(array $array, ?callable $predicate = null)
    {
        if (!$predicate) {
            \reset($array);
            return \key($array);
        }
        foreach ($array as $k => $v) {
            if ($predicate($v, $k, $array)) {
                return $k;
            }
        }
        return null;
    }
    /**
     * Returns the key of last item (matching the specified predicate if given) or null if there is no such item.
     * The $predicate has the signature `function (mixed $value, int|string $key, array $array): bool`.
     * @return int|string|null
     */
    public static function lastKey(array $array, ?callable $predicate = null)
    {
        \end($array);
        return $predicate ? self::firstKey(\array_reverse($array, \true), $predicate) : \key($array);
    }
    /**
     * Inserts the contents of the $inserted array into the $array immediately after the $key.
     * If $key is null (or does not exist), it is inserted at the beginning.
     * @param string|int|null $key
     */
    public static function insertBefore(array &$array, $key, array $inserted) : void
    {
        $offset = $key === null ? 0 : (int) self::getKeyOffset($array, $key);
        $array = \array_slice($array, 0, $offset, \true) + $inserted + \array_slice($array, $offset, count($array), \true);
    }
    /**
     * Inserts the contents of the $inserted array into the $array before the $key.
     * If $key is null (or does not exist), it is inserted at the end.
     * @param string|int|null $key
     */
    public static function insertAfter(array &$array, $key, array $inserted) : void
    {
        if ($key === null || ($offset = self::getKeyOffset($array, $key)) === null) {
            $offset = count($array) - 1;
        }
        $array = \array_slice($array, 0, $offset + 1, \true) + $inserted + \array_slice($array, $offset + 1, count($array), \true);
    }
    /**
     * Renames key in array.
     * @param string|int $oldKey
     * @param string|int $newKey
     */
    public static function renameKey(array &$array, $oldKey, $newKey) : bool
    {
        $offset = self::getKeyOffset($array, $oldKey);
        if ($offset === null) {
            return \false;
        }
        $val =& $array[$oldKey];
        $keys = \array_keys($array);
        $keys[$offset] = $newKey;
        $array = \array_combine($keys, $array);
        $array[$newKey] =& $val;
        return \true;
    }
    /**
     * Returns only those array items, which matches a regular expression $pattern.
     * @param  string[]  $array
     * @return string[]
     * @param bool|int $invert
     */
    public static function grep(
        array $array,
        /**
         * @language
         */
        string $pattern,
        $invert = \false
    ) : array
    {
        $flags = $invert ? \PREG_GREP_INVERT : 0;
        return Strings::pcre('preg_grep', [$pattern, $array, $flags]);
    }
    /**
     * Transforms multidimensional array to flat array.
     */
    public static function flatten(array $array, bool $preserveKeys = \false) : array
    {
        $res = [];
        $cb = $preserveKeys ? function ($v, $k) use(&$res) : void {
            $res[$k] = $v;
        } : function ($v) use(&$res) : void {
            $res[] = $v;
        };
        \array_walk_recursive($array, $cb);
        return $res;
    }
    /**
     * Checks if the array is indexed in ascending order of numeric keys from zero, a.k.a list.
     * @return ($value is list ? true : false)
     * @param mixed $value
     */
    public static function isList($value) : bool
    {
        $arrayIsListFunction = function (array $array) : bool {
            if (\function_exists('array_is_list')) {
                return \array_is_list($array);
            }
            if ($array === []) {
                return \true;
            }
            $current_key = 0;
            foreach ($array as $key => $noop) {
                if ($key !== $current_key) {
                    return \false;
                }
                ++$current_key;
            }
            return \true;
        };
        return is_array($value) && (\PHP_VERSION_ID < 80100 ? !$value || \array_keys($value) === \range(0, count($value) - 1) : $arrayIsListFunction($value));
    }
    /**
     * Reformats table to associative tree. Path looks like 'field|field[]field->field=field'.
     * @param  string|string[]  $path
     * @return mixed[]|\stdClass
     */
    public static function associate(array $array, $path)
    {
        $parts = is_array($path) ? $path : \preg_split('#(\\[\\]|->|=|\\|)#', $path, -1, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY);
        if (!$parts || $parts === ['->'] || $parts[0] === '=' || $parts[0] === '|') {
            throw new Nette\InvalidArgumentException("Invalid path '{$path}'.");
        }
        $res = $parts[0] === '->' ? new \stdClass() : [];
        foreach ($array as $rowOrig) {
            $row = (array) $rowOrig;
            $x =& $res;
            for ($i = 0; $i < count($parts); $i++) {
                $part = $parts[$i];
                if ($part === '[]') {
                    $x =& $x[];
                } elseif ($part === '=') {
                    if (isset($parts[++$i])) {
                        $x = $row[$parts[$i]];
                        $row = null;
                    }
                } elseif ($part === '->') {
                    if (isset($parts[++$i])) {
                        if ($x === null) {
                            $x = new \stdClass();
                        }
                        $x =& $x->{$row[$parts[$i]]};
                    } else {
                        $row = is_object($rowOrig) ? $rowOrig : (object) $row;
                    }
                } elseif ($part !== '|') {
                    $x =& $x[(string) $row[$part]];
                }
            }
            if ($x === null) {
                $x = $row;
            }
        }
        return $res;
    }
    /**
     * Normalizes array to associative array. Replace numeric keys with their values, the new value will be $filling.
     * @param mixed $filling
     */
    public static function normalize(array $array, $filling = null) : array
    {
        $res = [];
        foreach ($array as $k => $v) {
            $res[is_int($k) ? $v : $k] = is_int($k) ? $filling : $v;
        }
        return $res;
    }
    /**
     * Returns and removes the value of an item from an array. If it does not exist, it throws an exception,
     * or returns $default, if provided.
     * @template T
     * @param  array<T>  $array
     * @param mixed $default
     * @return ?T
     * @throws Nette\InvalidArgumentException if item does not exist and default value is not provided
     * @param string|int $key
     */
    public static function pick(array &$array, $key, $default = null)
    {
        if (\array_key_exists($key, $array)) {
            $value = $array[$key];
            unset($array[$key]);
            return $value;
        } elseif (\func_num_args() < 3) {
            throw new Nette\InvalidArgumentException("Missing item '{$key}'.");
        } else {
            return $default;
        }
    }
    /**
     * Tests whether at least one element in the array passes the test implemented by the provided function,
     * which has the signature `function ($value, $key, array $array): bool`.
     * @template K
     * @template V
     * @param  iterable<K, V> $array
     * @param  callable(V, K, ($array is array ? array<K, V> : iterable<K, V>)): bool $predicate
     */
    public static function some(iterable $array, callable $predicate) : bool
    {
        foreach ($array as $k => $v) {
            if ($predicate($v, $k, $array)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * Tests whether all elements in the array pass the test implemented by the provided function,
     * which has the signature `function ($value, $key, array $array): bool`.
     * @template K
     * @template V
     * @param  iterable<K, V> $array
     * @param  callable(V, K, ($array is array ? array<K, V> : iterable<K, V>)): bool $predicate
     */
    public static function every(iterable $array, callable $predicate) : bool
    {
        foreach ($array as $k => $v) {
            if (!$predicate($v, $k, $array)) {
                return \false;
            }
        }
        return \true;
    }
    /**
     * Returns a new array containing all key-value pairs matching the given $predicate.
     * The callback has the signature `function (mixed $value, int|string $key, array $array): bool`.
     * @template K of array-key
     * @template V
     * @param  array<K, V> $array
     * @param  callable(V, K, array<K, V>): bool $predicate
     * @return array<K, V>
     */
    public static function filter(array $array, callable $predicate) : array
    {
        $res = [];
        foreach ($array as $k => $v) {
            if ($predicate($v, $k, $array)) {
                $res[$k] = $v;
            }
        }
        return $res;
    }
    /**
     * Returns an array containing the original keys and results of applying the given transform function to each element.
     * The function has signature `function ($value, $key, array $array): mixed`.
     * @template K of array-key
     * @template V
     * @template R
     * @param  iterable<K, V> $array
     * @param  callable(V, K, ($array is array ? array<K, V> : iterable<K, V>)): R $transformer
     * @return array<K, R>
     */
    public static function map(iterable $array, callable $transformer) : array
    {
        $res = [];
        foreach ($array as $k => $v) {
            $res[$k] = $transformer($v, $k, $array);
        }
        return $res;
    }
    /**
     * Invokes all callbacks and returns array of results.
     * @param  callable[]  $callbacks
     */
    public static function invoke(iterable $callbacks, ...$args) : array
    {
        $res = [];
        foreach ($callbacks as $k => $cb) {
            $res[$k] = $cb(...$args);
        }
        return $res;
    }
    /**
     * Invokes method on every object in an array and returns array of results.
     * @param  object[]  $objects
     */
    public static function invokeMethod(iterable $objects, string $method, ...$args) : array
    {
        $res = [];
        foreach ($objects as $k => $obj) {
            $res[$k] = $obj->{$method}(...$args);
        }
        return $res;
    }
    /**
     * Copies the elements of the $array array to the $object object and then returns it.
     * @template T of object
     * @param  T  $object
     * @return T
     */
    public static function toObject(iterable $array, object $object) : object
    {
        foreach ($array as $k => $v) {
            $object->{$k} = $v;
        }
        return $object;
    }
    /**
     * Converts value to array key.
     * @return int|string
     * @param mixed $value
     */
    public static function toKey($value)
    {
        return \key([$value => null]);
    }
    /**
     * Returns copy of the $array where every item is converted to string
     * and prefixed by $prefix and suffixed by $suffix.
     * @param  string[]  $array
     * @return string[]
     */
    public static function wrap(array $array, string $prefix = '', string $suffix = '') : array
    {
        $res = [];
        foreach ($array as $k => $v) {
            $res[$k] = $prefix . $v . $suffix;
        }
        return $res;
    }
}
