<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector\Fixture;

use PhpParser\Node;

final class SkipSetNode
{
    /**
     * @var Node|null
     */
    private $node;

    public function setNode(Node $node): void
    {
        $this->node = $node;
    }

    public function getNode(): ?Node
    {
        return $this->node;
    }
}
