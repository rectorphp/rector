<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202407\Symfony\Component\Finder\Iterator;

/**
 * @author Jérémy Derussé <jeremy@derusse.com>
 *
 * @internal
 */
class LazyIterator implements \IteratorAggregate
{
    /**
     * @var \Closure
     */
    private $iteratorFactory;
    public function __construct(callable $iteratorFactory)
    {
        $this->iteratorFactory = \Closure::fromCallable($iteratorFactory);
    }
    public function getIterator() : \Traversable
    {
        yield from ($this->iteratorFactory)();
    }
}
