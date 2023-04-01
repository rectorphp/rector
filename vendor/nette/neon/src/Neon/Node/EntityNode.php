<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix202304\Nette\Neon\Node;

use RectorPrefix202304\Nette\Neon\Entity;
use RectorPrefix202304\Nette\Neon\Node;
/** @internal */
final class EntityNode extends Node
{
    /**
     * @var \Nette\Neon\Node
     */
    public $value;
    /**
     * @var mixed[]
     */
    public $attributes = [];
    public function __construct(Node $value, array $attributes = [])
    {
        $this->value = $value;
        /** @var ArrayItemNode[] */
        $this->attributes = $attributes;
    }
    public function toValue() : Entity
    {
        return new Entity($this->value->toValue(), ArrayItemNode::itemsToArray($this->attributes));
    }
    public function toString() : string
    {
        return $this->value->toString() . '(' . ($this->attributes ? ArrayItemNode::itemsToInlineString($this->attributes) : '') . ')';
    }
    public function &getIterator() : \Generator
    {
        (yield $this->value);
        foreach ($this->attributes as &$item) {
            (yield $item);
        }
    }
}
