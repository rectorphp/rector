<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

final class SkipAnnotationsOnChaoticMethods extends NodeVisitorAbstract
{
    /**
     * @return Node|null
     */
    public function enterNode(Node $node): ?Node
    {
        return $node;
    }
}

