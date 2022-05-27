<?php

declare (strict_types=1);
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
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Scope\PHPStanNodeScopeResolver
     */
    private $phpStanNodeScopeResolver;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ScopeAnalyzer
     */
    private $scopeAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\UnreachableStmtAnalyzer
     */
    private $unreachableStmtAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\NodeTypeResolver\PHPStan\Scope\PHPStanNodeScopeResolver $phpStanNodeScopeResolver, \Rector\Core\NodeAnalyzer\ScopeAnalyzer $scopeAnalyzer, \Rector\Core\NodeAnalyzer\UnreachableStmtAnalyzer $unreachableStmtAnalyzer, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->phpStanNodeScopeResolver = $phpStanNodeScopeResolver;
        $this->scopeAnalyzer = $scopeAnalyzer;
        $this->unreachableStmtAnalyzer = $unreachableStmtAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function refresh(\PhpParser\Node $node, \Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo, ?\PHPStan\Analyser\MutatingScope $mutatingScope) : void
    {
        // nothing to refresh
        if (!$this->scopeAnalyzer->hasScope($node)) {
            return;
        }
        if (!$mutatingScope instanceof \PHPStan\Analyser\MutatingScope) {
            /**
             * Node does not has Scope, while:
             *
             * 1. Node is Scope aware
             * 2. Its current Stmt is Reachable
             */
            $currentStmt = $this->betterNodeFinder->resolveCurrentStatement($node);
            if (!$this->unreachableStmtAnalyzer->isStmtPHPStanUnreachable($currentStmt)) {
                $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
                $errorMessage = \sprintf('Node "%s" with parent of "%s" is missing scope required for scope refresh.', \get_class($node), $parent instanceof \PhpParser\Node ? \get_class($parent) : null);
                throw new \Rector\Core\Exception\ShouldNotHappenException($errorMessage);
            }
        }
        // note from flight: when we traverse ClassMethod, the scope must be already in Class_, otherwise it crashes
        // so we need to somehow get a parent scope that is already in the same place the $node is
        if ($node instanceof \PhpParser\Node\Attribute) {
            // we'll have to fake-traverse 2 layers up, as PHPStan skips Scope for AttributeGroups and consequently Attributes
            $attributeGroup = new \PhpParser\Node\AttributeGroup([$node]);
            $node = new \PhpParser\Node\Stmt\Property(0, [], [], null, [$attributeGroup]);
        }
        $stmts = $this->resolveStmts($node);
        $this->phpStanNodeScopeResolver->processNodes($stmts, $smartFileInfo, $mutatingScope);
    }
    /**
     * @return Stmt[]
     */
    private function resolveStmts(\PhpParser\Node $node) : array
    {
        if ($node instanceof \PhpParser\Node\Stmt) {
            return [$node];
        }
        if ($node instanceof \PhpParser\Node\Expr) {
            return [new \PhpParser\Node\Stmt\Expression($node)];
        }
        $errorMessage = \sprintf('Complete parent node of "%s" be a stmt.', \get_class($node));
        throw new \Rector\Core\Exception\ShouldNotHappenException($errorMessage);
    }
}
