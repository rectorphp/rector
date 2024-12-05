<?php

declare (strict_types=1);
namespace Rector\Application;

use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\ClosureUse;
use PhpParser\Node\DeclareItem;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\StaticVar;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Static_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\UseItem;
use PHPStan\Analyser\MutatingScope;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeAnalyzer\ScopeAnalyzer;
use Rector\NodeTypeResolver\PHPStan\Scope\PHPStanNodeScopeResolver;
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
    public function __construct(PHPStanNodeScopeResolver $phpStanNodeScopeResolver, ScopeAnalyzer $scopeAnalyzer)
    {
        $this->phpStanNodeScopeResolver = $phpStanNodeScopeResolver;
        $this->scopeAnalyzer = $scopeAnalyzer;
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
        $stmts = $this->resolveStmts($node);
        $this->phpStanNodeScopeResolver->processNodes($stmts, $filePath, $mutatingScope);
    }
    public function reIndexNodeAttributes(Node $node) : void
    {
        if ($node instanceof FunctionLike) {
            /** @var ClassMethod|Function_|Closure $node */
            $node->params = \array_values($node->params);
            if ($node instanceof Closure) {
                $node->uses = \array_values($node->uses);
            }
        }
        if ($node instanceof CallLike) {
            /** @var FuncCall|MethodCall|New_|NullsafeMethodCall|StaticCall $node */
            $node->args = \array_values($node->args);
        }
        if ($node instanceof If_) {
            $node->elseifs = \array_values($node->elseifs);
        }
        if ($node instanceof TryCatch) {
            $node->catches = \array_values($node->catches);
        }
        if ($node instanceof Switch_) {
            $node->cases = \array_values($node->cases);
        }
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
        $errorMessage = \sprintf('Complete parent node of "%s" be a stmt.', \get_class($node));
        throw new ShouldNotHappenException($errorMessage);
    }
}
