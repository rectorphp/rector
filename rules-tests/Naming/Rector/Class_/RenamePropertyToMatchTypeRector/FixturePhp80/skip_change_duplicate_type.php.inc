<?php

namespace Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\FixturePhp80;

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

    /**
     * @return Name|Identifier
     */
    public function getNameNode(): Node
    {
        return $this->nameNode;
    }

    public function getParentNode(): Node
    {
        return $this->parentNode;
    }
}
