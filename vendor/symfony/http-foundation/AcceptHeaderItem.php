<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\HttpFoundation;

/**
 * Represents an Accept-* header item.
 *
 * @author Jean-François Simon <contact@jfsimon.fr>
 */
class AcceptHeaderItem
{
    private $value;
    private $quality = 1.0;
    private $index = 0;
    private $attributes = [];
    public function __construct(string $value, array $attributes = [])
    {
        $this->value = $value;
        foreach ($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }
    }
    /**
     * Builds an AcceptHeaderInstance instance from a string.
     *
     * @return self
     * @param string|null $itemValue
     */
    public static function fromString($itemValue)
    {
        $parts = \RectorPrefix20211020\Symfony\Component\HttpFoundation\HeaderUtils::split($itemValue ?? '', ';=');
        $part = \array_shift($parts);
        $attributes = \RectorPrefix20211020\Symfony\Component\HttpFoundation\HeaderUtils::combine($parts);
        return new self($part[0], $attributes);
    }
    /**
     * Returns header value's string representation.
     *
     * @return string
     */
    public function __toString()
    {
        $string = $this->value . ($this->quality < 1 ? ';q=' . $this->quality : '');
        if (\count($this->attributes) > 0) {
            $string .= '; ' . \RectorPrefix20211020\Symfony\Component\HttpFoundation\HeaderUtils::toString($this->attributes, ';');
        }
        return $string;
    }
    /**
     * Set the item value.
     *
     * @return $this
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
    /**
     * Returns the item value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
    /**
     * Set the item quality.
     *
     * @return $this
     * @param float $quality
     */
    public function setQuality($quality)
    {
        $this->quality = $quality;
        return $this;
    }
    /**
     * Returns the item quality.
     *
     * @return float
     */
    public function getQuality()
    {
        return $this->quality;
    }
    /**
     * Set the item index.
     *
     * @return $this
     * @param int $index
     */
    public function setIndex($index)
    {
        $this->index = $index;
        return $this;
    }
    /**
     * Returns the item index.
     *
     * @return int
     */
    public function getIndex()
    {
        return $this->index;
    }
    /**
     * Tests if an attribute exists.
     *
     * @return bool
     * @param string $name
     */
    public function hasAttribute($name)
    {
        return isset($this->attributes[$name]);
    }
    /**
     * Returns an attribute by its name.
     *
     * @param mixed $default
     *
     * @return mixed
     * @param string $name
     */
    public function getAttribute($name, $default = null)
    {
        return $this->attributes[$name] ?? $default;
    }
    /**
     * Returns all attributes.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
    /**
     * Set an attribute.
     *
     * @return $this
     * @param string $name
     * @param string $value
     */
    public function setAttribute($name, $value)
    {
        if ('q' === $name) {
            $this->quality = (float) $value;
        } else {
            $this->attributes[$name] = $value;
        }
        return $this;
    }
}
