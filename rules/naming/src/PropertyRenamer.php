<?php

declare(strict_types=1);

namespace Rector\Naming;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\VarLikeIdentifier;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\Core\Rector\AbstractRector\NameResolverTrait;
use Rector\Core\Rector\AbstractRector\NodeTypeResolverTrait;
use Rector\Naming\Guard\BreakingVariableRenameGuard;
use Rector\Naming\ValueObject\PropertyRename;

final class PropertyRenamer
{
    use NameResolverTrait;
    use NodeTypeResolverTrait;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var BreakingVariableRenameGuard
     */
    private $breakingVariableRenameGuard;

    public function __construct(
        CallableNodeTraverser $callableNodeTraverser,
        BreakingVariableRenameGuard $breakingVariableRenameGuard
    ) {
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->breakingVariableRenameGuard = $breakingVariableRenameGuard;
    }

    public function rename(PropertyRename $propertyRename): void
    {
        if ($this->breakingVariableRenameGuard->shouldSkipProperty($propertyRename)) {
            return;
        }

        $onlyPropertyProperty = $propertyRename->getProperty()->props[0];
        $onlyPropertyProperty->name = new VarLikeIdentifier($propertyRename->getExpectedName());
        $this->renamePropertyFetchesInClass($propertyRename);
    }

    private function renamePropertyFetchesInClass(PropertyRename $propertyRename): void
    {
        // 1. replace property fetch rename in whole class
        $this->callableNodeTraverser->traverseNodesWithCallable(
            [$propertyRename->getClassLike()],
            function (Node $node) use ($propertyRename): ?PropertyFetch {
                if (! $this->isLocalPropertyFetchNamed($node, $propertyRename->getCurrentName())) {
                    return null;
                }

                /** @var PropertyFetch $node */
                $node->name = new Identifier($propertyRename->getExpectedName());
                return $node;
            }
        );
    }
}
