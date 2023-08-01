<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix202308\Nette\Iterators;

use RectorPrefix202308\Nette;
/**
 * Smarter caching iterator.
 *
 * @property-read bool $first
 * @property-read bool $last
 * @property-read bool $empty
 * @property-read bool $odd
 * @property-read bool $even
 * @property-read int $counter
 * @property-read mixed $nextKey
 * @property-read mixed $nextValue
 */
class CachingIterator extends \CachingIterator implements \Countable
{
    use Nette\SmartObject;
    /** @var int */
    private $counter = 0;
    public function __construct($iterator)
    {
        if (\is_array($iterator) || $iterator instanceof \stdClass) {
            $iterator = new \ArrayIterator($iterator);
        } elseif ($iterator instanceof \IteratorAggregate) {
            do {
                $iterator = $iterator->getIterator();
            } while ($iterator instanceof \IteratorAggregate);
            \assert($iterator instanceof \Iterator);
        } elseif ($iterator instanceof \Iterator) {
        } elseif ($iterator instanceof \Traversable) {
            $iterator = new \IteratorIterator($iterator);
        } else {
            throw new Nette\InvalidArgumentException(\sprintf('Invalid argument passed to %s; array or Traversable expected, %s given.', self::class, \is_object($iterator) ? \get_class($iterator) : \gettype($iterator)));
        }
        parent::__construct($iterator, 0);
    }
    /**
     * Is the current element the first one?
     */
    public function isFirst(?int $gridWidth = null) : bool
    {
        return $this->counter === 1 || $gridWidth && $this->counter !== 0 && ($this->counter - 1) % $gridWidth === 0;
    }
    /**
     * Is the current element the last one?
     */
    public function isLast(?int $gridWidth = null) : bool
    {
        return !$this->hasNext() || $gridWidth && $this->counter % $gridWidth === 0;
    }
    /**
     * Is the iterator empty?
     */
    public function isEmpty() : bool
    {
        return $this->counter === 0;
    }
    /**
     * Is the counter odd?
     */
    public function isOdd() : bool
    {
        return $this->counter % 2 === 1;
    }
    /**
     * Is the counter even?
     */
    public function isEven() : bool
    {
        return $this->counter % 2 === 0;
    }
    /**
     * Returns the counter.
     */
    public function getCounter() : int
    {
        return $this->counter;
    }
    /**
     * Returns the count of elements.
     */
    public function count() : int
    {
        $inner = $this->getInnerIterator();
        if ($inner instanceof \Countable) {
            return $inner->count();
        } else {
            throw new Nette\NotSupportedException('Iterator is not countable.');
        }
    }
    /**
     * Forwards to the next element.
     */
    public function next() : void
    {
        parent::next();
        if (parent::valid()) {
            $this->counter++;
        }
    }
    /**
     * Rewinds the Iterator.
     */
    public function rewind() : void
    {
        parent::rewind();
        $this->counter = parent::valid() ? 1 : 0;
    }
    /**
     * Returns the next key.
     * @return mixed
     */
    public function getNextKey()
    {
        return $this->getInnerIterator()->key();
    }
    /**
     * Returns the next element.
     * @return mixed
     */
    public function getNextValue()
    {
        return $this->getInnerIterator()->current();
    }
}
