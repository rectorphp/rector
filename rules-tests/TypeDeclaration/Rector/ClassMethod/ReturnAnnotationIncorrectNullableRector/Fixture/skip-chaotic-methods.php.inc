<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnAnnotationIncorrectNullableRector\Fixture;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

final class SkipAnnotationsOnChaoticMethods extends NodeVisitorAbstract
{
    /**
     * @return Node
     */
    public function enterNode(Node $node): ?Node
    {
        return $node;
    }
}

