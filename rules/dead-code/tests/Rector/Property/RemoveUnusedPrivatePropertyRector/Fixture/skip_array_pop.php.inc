<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class SkipArrayPop
{
    /**
     * @var ClassLike[]|null[]
     */
    private $classStack = [];

    private $classLike;

    public function leaveNode(Node $node)
    {
        $classLike = array_pop($this->classStack);
        $node->setAttribute(AttributeKey::CLASS_NODE, $classLike);
    }

    public function processClass(Node $node): void
    {
        $this->classStack[] = $this->classLike;
    }
}
