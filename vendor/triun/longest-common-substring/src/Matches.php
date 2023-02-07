<?php

namespace Triun\LongestCommonSubstring;

use Iterator;
use Countable;
use ArrayAccess;
use JsonSerializable;
use RuntimeException;

/**
 * Class Matches
 *
 * @package Triun\LongestCommonSubstring
 */
class Matches implements ArrayAccess, Iterator, JsonSerializable, Countable
{
    /**
     * @var \Triun\LongestCommonSubstring\Match[]
     */
    protected $items;

    /**
     * @var int
     */
    private $position = 0;

    /**
     * Matches constructor.
     *
     * @param \Triun\LongestCommonSubstring\Match[] $items
     */
    public function __construct(array $items)
    {
        $this->items = array_values($items);
    }

    /**
     * Has any match.
     *
     * @return bool
     */
    public function has()
    {
        return count($this->items) > 0;
    }

    /**
     * @return bool
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * Get first match object.
     *
     * @return \Triun\LongestCommonSubstring\Match
     */
    public function first()
    {
        return array_key_exists(0, $this->items) ? $this->items[0] : null;
    }

    /**
     * Get all matches objects.
     *
     * @return \Triun\LongestCommonSubstring\Match[]
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Get first value.
     *
     * @return string
     */
    public function firstValue()
    {
        if (!$this->has()) {
            return null;
        }

        return $this->items[0]->value;
    }

    /**
     * Get all matches values.
     *
     * @return string[]
     */
    public function values()
    {
        return array_map(function (Match $item) {
            return $item->value;
        }, $this->items);
    }

    /**
     * @return string[]
     */
    public function unique()
    {
        return array_values(array_unique($this->values()));
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->firstValue()?: '';
    }

    /**
     * Convert instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return array_map(function (Match $item) {
            return $item->toArray();
        }, $this->items);
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Convert instance to JSON.
     *
     * @param  int $options
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Determine if the given offset exists.
     *
     * @param  string $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->items);
    }

    /**
     * Get the value for a given offset.
     *
     * @param  string $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    /**
     * Set the value at the given offset.
     *
     * @param  string $offset
     * @param  mixed  $value
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        throw new RuntimeException('Read only');
    }

    /**
     * Unset the value at the given offset.
     *
     * @param  string $offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        throw new RuntimeException('Read only');
    }

    /**
     * Return the current element
     *
     * @return mixed|\Triun\LongestCommonSubstring\Match
     */
    public function current()
    {
        return $this->items[$this->position];
    }

    /**
     * Move forward to next element
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * Return the key of the current element
     *
     * @return mixed|int
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Checks if current position is valid
     *
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return $this->position < $this->count();
    }

    /**
     * Rewind the Iterator to the first element
     */
    public function rewind()
    {
        $this->position = 0;
    }
}
