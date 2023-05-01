<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix202305\Nette\Neon;

/**
 * @implements \IteratorAggregate<Node>
 */
abstract class Node implements \IteratorAggregate
{
    /**
     * @var int|null
     */
    public $startTokenPos;
    /**
     * @var int|null
     */
    public $endTokenPos;
    /**
     * @var int|null
     */
    public $startLine;
    /**
     * @var int|null
     */
    public $endLine;
    /**
     * @return mixed
     */
    public abstract function toValue();
    public abstract function toString() : string;
    public function &getIterator() : \Generator
    {
        return;
        yield;
    }
}
