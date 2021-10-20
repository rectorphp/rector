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

// Help opcache.preload discover always-needed symbols
\class_exists(\RectorPrefix20211020\Symfony\Component\HttpFoundation\AcceptHeaderItem::class);
/**
 * Represents an Accept-* header.
 *
 * An accept header is compound with a list of items,
 * sorted by descending quality.
 *
 * @author Jean-François Simon <contact@jfsimon.fr>
 */
class AcceptHeader
{
    /**
     * @var AcceptHeaderItem[]
     */
    private $items = [];
    /**
     * @var bool
     */
    private $sorted = \true;
    /**
     * @param AcceptHeaderItem[] $items
     */
    public function __construct(array $items)
    {
        foreach ($items as $item) {
            $this->add($item);
        }
    }
    /**
     * Builds an AcceptHeader instance from a string.
     *
     * @return self
     * @param string|null $headerValue
     */
    public static function fromString($headerValue)
    {
        $index = 0;
        $parts = \RectorPrefix20211020\Symfony\Component\HttpFoundation\HeaderUtils::split($headerValue ?? '', ',;=');
        return new self(\array_map(function ($subParts) use(&$index) {
            $part = \array_shift($subParts);
            $attributes = \RectorPrefix20211020\Symfony\Component\HttpFoundation\HeaderUtils::combine($subParts);
            $item = new \RectorPrefix20211020\Symfony\Component\HttpFoundation\AcceptHeaderItem($part[0], $attributes);
            $item->setIndex($index++);
            return $item;
        }, $parts));
    }
    /**
     * Returns header value's string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return \implode(',', $this->items);
    }
    /**
     * Tests if header has given value.
     *
     * @return bool
     * @param string $value
     */
    public function has($value)
    {
        return isset($this->items[$value]);
    }
    /**
     * Returns given value's item, if exists.
     *
     * @return AcceptHeaderItem|null
     * @param string $value
     */
    public function get($value)
    {
        return $this->items[$value] ?? $this->items[\explode('/', $value)[0] . '/*'] ?? $this->items['*/*'] ?? $this->items['*'] ?? null;
    }
    /**
     * Adds an item.
     *
     * @return $this
     * @param \Symfony\Component\HttpFoundation\AcceptHeaderItem $item
     */
    public function add($item)
    {
        $this->items[$item->getValue()] = $item;
        $this->sorted = \false;
        return $this;
    }
    /**
     * Returns all items.
     *
     * @return AcceptHeaderItem[]
     */
    public function all()
    {
        $this->sort();
        return $this->items;
    }
    /**
     * Filters items on their value using given regex.
     *
     * @return self
     * @param string $pattern
     */
    public function filter($pattern)
    {
        return new self(\array_filter($this->items, function (\RectorPrefix20211020\Symfony\Component\HttpFoundation\AcceptHeaderItem $item) use($pattern) {
            return \preg_match($pattern, $item->getValue());
        }));
    }
    /**
     * Returns first item.
     *
     * @return AcceptHeaderItem|null
     */
    public function first()
    {
        $this->sort();
        return !empty($this->items) ? \reset($this->items) : null;
    }
    /**
     * Sorts items by descending quality.
     */
    private function sort() : void
    {
        if (!$this->sorted) {
            \uasort($this->items, function (\RectorPrefix20211020\Symfony\Component\HttpFoundation\AcceptHeaderItem $a, \RectorPrefix20211020\Symfony\Component\HttpFoundation\AcceptHeaderItem $b) {
                $qA = $a->getQuality();
                $qB = $b->getQuality();
                if ($qA === $qB) {
                    return $a->getIndex() > $b->getIndex() ? 1 : -1;
                }
                return $qA > $qB ? -1 : 1;
            });
            $this->sorted = \true;
        }
    }
}
