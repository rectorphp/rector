<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20220418\Nette\Iterators;

/**
 * Applies the callback to the elements of the inner iterator.
 */
class Mapper extends \IteratorIterator
{
    /** @var callable */
    private $callback;
    public function __construct(\Traversable $iterator, callable $callback)
    {
        parent::__construct($iterator);
        $this->callback = $callback;
    }
    #[\ReturnTypeWillChange]
    public function current()
    {
        return ($this->callback)(parent::current(), parent::key());
    }
}
