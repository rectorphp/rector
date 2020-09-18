<?php

declare(strict_types=1);

namespace Rector\Naming;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\VarLikeIdentifier;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\Naming\Guard\BreakingVariableRenameGuard;
use Rector\Naming\ValueObject\PropertyRename;
use Rector\NodeNameResolver\NodeNameResolver;

final class PropertyRenamer
{
    /**
     * @var bool
     */
    private $isRenamed = false;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var BreakingVariableRenameGuard
     */
    private $breakingVariableRenameGuard;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(
        CallableNodeTraverser $callableNodeTraverser,
        BreakingVariableRenameGuard $breakingVariableRenameGuard,
        NodeNameResolver $nodeNameResolver
    ) {
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->breakingVariableRenameGuard = $breakingVariableRenameGuard;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function rename(PropertyRename $propertyRename): void
    {
        $this->isRenamed = false;

        if ($this->breakingVariableRenameGuard->shouldSkipProperty($propertyRename)) {
            return;
        }

        $onlyPropertyProperty = $propertyRename->getProperty()->props[0];
        $onlyPropertyProperty->name = new VarLikeIdentifier($propertyRename->getExpectedName());
        $this->renamePropertyFetchesInClass($propertyRename);

        $this->isRenamed = true;
    }

    public function isRenamed(): bool
    {
        return $this->isRenamed;
    }

    private function renamePropertyFetchesInClass(PropertyRename $propertyRename): void
    {
        // 1. replace property fetch rename in whole class
        $this->callableNodeTraverser->traverseNodesWithCallable(
            [$propertyRename->getClassLike()],
            function (Node $node) use ($propertyRename): ?PropertyFetch {
                if (! $this->nodeNameResolver->isLocalPropertyFetchNamed($node, $propertyRename->getCurrentName())) {
                    return null;
                }

                /** @var PropertyFetch $node */
                $node->name = new Identifier($propertyRename->getExpectedName());
                return $node;
            }
        );
    }
}
