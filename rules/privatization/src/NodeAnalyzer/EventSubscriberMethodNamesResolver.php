<?php

declare(strict_types=1);

namespace Rector\Privatization\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;

final class EventSubscriberMethodNamesResolver
{
    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    public function __construct(CallableNodeTraverser $callableNodeTraverser)
    {
        $this->callableNodeTraverser = $callableNodeTraverser;
    }

    /**
     * @return string[]
     */
    public function resolveFromClassMethod(ClassMethod $classMethod): array
    {
        $methodNames = [];
        $this->callableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use (
            &$methodNames
        ): ?void {
            if (! $node instanceof ArrayItem) {
                return null;
            }

            if (! $node->value instanceof String_) {
                return null;
            }

            $methodNames[] = $node->value->value;
        });

        return $methodNames;
    }
}
