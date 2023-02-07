<?php

namespace Triun\LongestCommonSubstring;

use Serializable;
use JsonSerializable;

/**
 * Class Match
 *
 * @package Triun\LongestCommonSubstring
 */
class Match implements JsonSerializable, Serializable
{
    /**
     * @var string
     */
    public $value;

    /**
     * @var int
     */
    public $length;

    /**
     * @var int[]
     */
    public $indexes = [];

    /**
     * Match constructor.
     *
     * @param int|null   $length
     * @param array|null $indexes
     */
    public function __construct(array $indexes = null, int $length = null)
    {
        $this->length = $length;
        $this->indexes = $indexes;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return Match
     */
    public function setValue(string $value): Match
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return (array)$this;
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @return string
     */
    public function serialize(): string
    {
        return serialize([
            $this->value,
            $this->length,
            $this->indexes,
        ]);
    }

    /**
     * @param string $data
     */
    public function unserialize($data)
    {
        list(
            $this->value,
            $this->length,
            $this->indexes
            ) = unserialize($data);
    }

    /**
     * @param int      $key
     * @param int|null $default
     *
     * @return int|null
     */
    public function index(int $key = 0, int $default = null)
    {
        return array_key_exists($key, $this->indexes) ? $this->indexes[$key] : $default;
    }
}
