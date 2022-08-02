<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix202208\Nette\Neon;

/**
 * @implements \IteratorAggregate<Node>
 */
abstract class Node implements \IteratorAggregate
{
    /** @var ?int */
    public $startTokenPos;
    /** @var ?int */
    public $endTokenPos;
    /** @var ?int */
    public $startLine;
    /** @var ?int */
    public $endLine;
    /** @return mixed */
    public abstract function toValue();
    public abstract function toString() : string;
    public function &getIterator() : \Generator
    {
        return;
        yield;
    }
}
