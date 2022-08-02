<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix202208\Nette\Neon\Node;

use RectorPrefix202208\Nette\Neon\Node;
/** @internal */
abstract class ArrayNode extends Node
{
    /** @var ArrayItemNode[] */
    public $items = [];
    /** @return mixed[] */
    public function toValue() : array
    {
        return ArrayItemNode::itemsToArray($this->items);
    }
    public function &getIterator() : \Generator
    {
        foreach ($this->items as &$item) {
            (yield $item);
        }
    }
}
