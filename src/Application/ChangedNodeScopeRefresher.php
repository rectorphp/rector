<?php

declare(strict_types=1);

namespace Rector\Core\Application;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\MutatingScope;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeAnalyzer\ScopeAnalyzer;
use Rector\Core\NodeAnalyzer\UnreachableStmtAnalyzer;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Scope\PHPStanNodeScopeResolver;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * In case of changed node, we need to re-traverse the PHPStan Scope to make all the new nodes aware of what is going on.
 */
final class ChangedNodeScopeRefresher
{
    public function __construct(
        private readonly PHPStanNodeScopeResolver $phpStanNodeScopeResolver,
        private readonly ScopeAnalyzer $scopeAnalyzer,
        private readonly UnreachableStmtAnalyzer $unreachableStmtAnalyzer,
        private readonly BetterNodeFinder $betterNodeFinder
    ) {
    }

    public function refresh(Node $node, SmartFileInfo $smartFileInfo, ?MutatingScope $mutatingScope): void
    {
        // nothing to refresh
        if (! $this->scopeAnalyzer->hasScope($node)) {
            return;
        }

        if (! $mutatingScope instanceof MutatingScope) {
            /**
             * Node does not has Scope, while:
             *
             * 1. Node is Scope aware
             * 2. Its current Stmt is Reachable
             */
            $currentStmt = $this->betterNodeFinder->resolveCurrentStatement($node);
            if (! $this->unreachableStmtAnalyzer->isStmtPHPStanUnreachable($currentStmt)) {
                $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
                $errorMessage = sprintf(
                    'Node "%s" with parent of "%s" is missing scope required for scope refresh.',
                    $node::class,
                    $parent instanceof Node ? $parent::class : null
                );

                throw new ShouldNotHappenException($errorMessage);
            }
        }

        // note from flight: when we traverse ClassMethod, the scope must be already in Class_, otherwise it crashes
        // so we need to somehow get a parent scope that is already in the same place the $node is

        if ($node instanceof Attribute) {
            // we'll have to fake-traverse 2 layers up, as PHPStan skips Scope for AttributeGroups and consequently Attributes
            $attributeGroup = new AttributeGroup([$node]);
            $node = new Property(0, [], [], null, [$attributeGroup]);
        }

        $stmts = $this->resolveStmts($node);
        $this->phpStanNodeScopeResolver->processNodes($stmts, $smartFileInfo, $mutatingScope);
    }

    /**
     * @return Stmt[]
     */
    private function resolveStmts(Node $node): array
    {
        if ($node instanceof Stmt) {
            return [$node];
        }

        if ($node instanceof Expr) {
            return [new Expression($node)];
        }

        $errorMessage = sprintf('Complete parent node of "%s" be a stmt.', $node::class);
        throw new ShouldNotHappenException($errorMessage);
    }
}
