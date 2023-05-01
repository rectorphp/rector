<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix202305\Nette\Neon\Node;

/** @internal */
final class BlockArrayNode extends ArrayNode
{
    /**
     * @var string
     */
    public $indentation = '';
    public function __construct(string $indentation = '')
    {
        $this->indentation = $indentation;
    }
    public function toString() : string
    {
        if (\count($this->items) === 0) {
            return '[]';
        }
        $res = ArrayItemNode::itemsToBlockString($this->items);
        return \preg_replace('#^(?=.)#m', $this->indentation, $res);
    }
}
