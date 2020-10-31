<?php

declare(strict_types=1);

namespace Rector\Testing\PHPUnit\Runnable\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeVisitorAbstract;

final class ClassLikeNameCollectingNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var string[]
     */
    private $classLikeNames = [];

    /**
     * @return string[]
     */
    public function getClassLikeNames(): array
    {
        return $this->classLikeNames;
    }

    public function enterNode(Node $node)
    {
        if (! $node instanceof ClassLike) {
            return null;
        }

        if ($node->name === null) {
            return null;
        }

        $this->classLikeNames[] = $node->name->toString();
        return null;
    }
}
