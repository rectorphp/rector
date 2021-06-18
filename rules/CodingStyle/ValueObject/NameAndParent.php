<?php

declare(strict_types=1);

namespace Rector\CodingStyle\ValueObject;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;

final class NameAndParent
{
    /**
     * @param Name|Identifier $nameNode
     */
    public function __construct(
        private Node $nameNode,
        private Node $parentNode
    ) {
    }

    public function getNameNode(): Identifier | Name
    {
        return $this->nameNode;
    }

    public function getParentNode(): Node
    {
        return $this->parentNode;
    }
}
