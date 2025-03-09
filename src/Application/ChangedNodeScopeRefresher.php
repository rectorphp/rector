<?php

declare (strict_types=1);
namespace Rector\Application;

use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\ClosureUse;
use PhpParser\Node\DeclareItem;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Param;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\StaticVar;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Static_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\UseItem;
use PHPStan\Analyser\MutatingScope;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeAnalyzer\ScopeAnalyzer;
use Rector\NodeTypeResolver\PHPStan\Scope\PHPStanNodeScopeResolver;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
/**
 * In case of changed node, we need to re-traverse the PHPStan Scope to make all the new nodes aware of what is going on.
 */
final class ChangedNodeScopeRefresher
{
    /**
     * @readonly
     */
    private PHPStanNodeScopeResolver $phpStanNodeScopeResolver;
    /**
     * @readonly
     */
    private ScopeAnalyzer $scopeAnalyzer;
    /**
     * @readonly
     */
    private SimpleCallableNodeTraverser $simpleCallableNodeTraverser;
    public function __construct(PHPStanNodeScopeResolver $phpStanNodeScopeResolver, ScopeAnalyzer $scopeAnalyzer, SimpleCallableNodeTraverser $simpleCallableNodeTraverser)
    {
        $this->phpStanNodeScopeResolver = $phpStanNodeScopeResolver;
        $this->scopeAnalyzer = $scopeAnalyzer;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
    }
    public function refresh(Node $node, string $filePath, ?MutatingScope $mutatingScope) : void
    {
        // nothing to refresh
        if (!$this->scopeAnalyzer->isRefreshable($node)) {
            return;
        }
        if (!$mutatingScope instanceof MutatingScope) {
            $errorMessage = \sprintf('Node "%s" with is missing scope required for scope refresh', \get_class($node));
            throw new ShouldNotHappenException($errorMessage);
        }
        // reindex stmt_key already covered on StmtKeyNodeVisitor on next processNodes()
        // so set flag $reIndexStmtKey to false to avoid double loop
        \Rector\Application\NodeAttributeReIndexer::reIndexNodeAttributes($node, \false);
        $stmts = $this->resolveStmts($node);
        $this->phpStanNodeScopeResolver->processNodes($stmts, $filePath, $mutatingScope);
    }
    /**
     * @return Stmt[]
     */
    private function resolveStmts(Node $node) : array
    {
        if ($node instanceof Stmt) {
            return [$node];
        }
        if ($node instanceof Expr) {
            return [new Expression($node)];
        }
        // moved from Expr/Stmt to directly under Node on PHPParser 5
        if ($node instanceof ArrayItem) {
            return [new Expression(new Array_([$node]))];
        }
        if ($node instanceof ClosureUse) {
            $closure = new Closure();
            $closure->uses[] = $node;
            return [new Expression($closure)];
        }
        if ($node instanceof DeclareItem) {
            return [new Declare_([$node])];
        }
        if ($node instanceof PropertyItem) {
            return [new Property(Modifiers::PUBLIC, [$node])];
        }
        if ($node instanceof StaticVar) {
            return [new Static_([$node])];
        }
        if ($node instanceof UseItem) {
            return [new Use_([$node])];
        }
        if ($node instanceof Param) {
            $closure = new Closure();
            $closure->params[] = $node;
            return [new Expression($closure)];
        }
        if ($node instanceof AttributeGroup) {
            $class = new Class_(null);
            $class->attrGroups[] = $node;
            $this->setLineAttributesOnClass($class, $node);
            return [$class];
        }
        if ($node instanceof Attribute) {
            $class = new Class_(null);
            $class->attrGroups[] = new AttributeGroup([$node]);
            $this->setLineAttributesOnClass($class, $node);
            return [$class];
        }
        if ($node instanceof Arg) {
            $class = new Class_(null, [], ['startLine' => $node->getStartLine(), 'endLine' => $node->getEndLine()]);
            $new = new New_($class, [$node]);
            return [new Expression($new)];
        }
        $errorMessage = \sprintf('Complete parent node of "%s" be a stmt.', \get_class($node));
        throw new ShouldNotHappenException($errorMessage);
    }
    /**
     * @param \PhpParser\Node\Attribute|\PhpParser\Node\AttributeGroup $node
     */
    private function setLineAttributesOnClass(Class_ $class, $node) : void
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable([$class], function (Node $subNode) use($node) : Node {
            $subNode->setAttributes(['startLine' => $node->getStartLine(), 'endLine' => $node->getEndLine()]);
            return $subNode;
        });
    }
}
