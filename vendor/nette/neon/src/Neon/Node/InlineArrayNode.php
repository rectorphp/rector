<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix202305\Nette\Neon\Node;

/** @internal */
final class InlineArrayNode extends ArrayNode
{
    /**
     * @var string
     */
    public $bracket;
    public function __construct(string $bracket)
    {
        $this->bracket = $bracket;
    }
    public function toString() : string
    {
        return $this->bracket . ArrayItemNode::itemsToInlineString($this->items) . ['[' => ']', '{' => '}', '(' => ')'][$this->bracket];
    }
}
