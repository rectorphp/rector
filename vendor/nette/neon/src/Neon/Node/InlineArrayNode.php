<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20220501\Nette\Neon\Node;

/** @internal */
final class InlineArrayNode extends \RectorPrefix20220501\Nette\Neon\Node\ArrayNode
{
    /** @var string */
    public $bracket;
    public function __construct(string $bracket)
    {
        $this->bracket = $bracket;
    }
    public function toString() : string
    {
        return $this->bracket . \RectorPrefix20220501\Nette\Neon\Node\ArrayItemNode::itemsToInlineString($this->items) . ['[' => ']', '{' => '}', '(' => ')'][$this->bracket];
    }
}
