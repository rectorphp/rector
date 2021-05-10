<?php

declare(strict_types=1);

namespace Rector\Privatization\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class EventSubscriberMethodNamesResolver
{
    public function __construct(
        private SimpleCallableNodeTraverser $simpleCallableNodeTraverser
    ) {
    }

    /**
     * @return string[]
     */
    public function resolveFromClassMethod(ClassMethod $classMethod): array
    {
        $methodNames = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable(
            (array) $classMethod->stmts,
            function (Node $node) use (&$methodNames) {
                if (! $node instanceof ArrayItem) {
                    return null;
                }

                if (! $node->value instanceof String_) {
                    return null;
                }

                $methodNames[] = $node->value->value;
            }
        );

        return $methodNames;
    }
}
