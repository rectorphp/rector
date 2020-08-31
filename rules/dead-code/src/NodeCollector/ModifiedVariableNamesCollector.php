<?php

declare(strict_types=1);

namespace Rector\DeadCode\NodeCollector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ModifiedVariableNamesCollector
{
    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(CallableNodeTraverser $callableNodeTraverser, NodeNameResolver $nodeNameResolver)
    {
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * @return string[]
     */
    public function collectModifiedVariableNames(Node $node): array
    {
        $modifiedVariableNames = [];

        $this->callableNodeTraverser->traverseNodesWithCallable($node, function (Node $node) use (
            &$modifiedVariableNames
        ): ?void {
            if ($this->isVariableOverriddenInAssign($node)) {
                /** @var Assign $node */
                $variableName = $this->nodeNameResolver->getName($node->var);
                if ($variableName === null) {
                    return null;
                }

                $modifiedVariableNames[] = $variableName;
            }

            if ($this->isVariableChangedInReference($node)) {
                /** @var Arg $node */
                $variableName = $this->nodeNameResolver->getName($node->value);
                if ($variableName === null) {
                    return null;
                }

                $modifiedVariableNames[] = $variableName;
            }
        });

        return $modifiedVariableNames;
    }

    private function isVariableOverriddenInAssign(Node $node): bool
    {
        if (! $node instanceof Assign) {
            return false;
        }

        return $node->var instanceof Variable;
    }

    private function isVariableChangedInReference(Node $node): bool
    {
        if (! $node instanceof Arg) {
            return false;
        }

        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentNode instanceof FuncCall) {
            return false;
        }

        return $this->nodeNameResolver->isNames($parentNode, ['array_shift', 'array_pop']);
    }
}
