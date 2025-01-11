<?php

declare (strict_types=1);
namespace Rector\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ErrorSuppress;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\FunctionLike;
use Rector\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeNestingScope\ContextAnalyzer;
use Rector\Php72\ValueObject\ListAndEach;
use Rector\PhpParser\Node\BetterNodeFinder;
final class AssignManipulator
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private PropertyFetchAnalyzer $propertyFetchAnalyzer;
    /**
     * @readonly
     */
    private ContextAnalyzer $contextAnalyzer;
    public function __construct(NodeNameResolver $nodeNameResolver, BetterNodeFinder $betterNodeFinder, PropertyFetchAnalyzer $propertyFetchAnalyzer, ContextAnalyzer $contextAnalyzer)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->contextAnalyzer = $contextAnalyzer;
    }
    /**
     * Matches:
     * list([1, 2]) = each($items)
     */
    public function matchListAndEach(Assign $assign) : ?ListAndEach
    {
        // could be behind error suppress
        if ($assign->expr instanceof ErrorSuppress) {
            $errorSuppress = $assign->expr;
            $bareExpr = $errorSuppress->expr;
        } else {
            $bareExpr = $assign->expr;
        }
        if (!$bareExpr instanceof FuncCall) {
            return null;
        }
        if (!$assign->var instanceof List_) {
            return null;
        }
        if (!$this->nodeNameResolver->isName($bareExpr, 'each')) {
            return null;
        }
        // no placeholders
        if ($bareExpr->isFirstClassCallable()) {
            return null;
        }
        return new ListAndEach($assign->var, $bareExpr);
    }
    /**
     * @api doctrine
     * @return array<PropertyFetch|StaticPropertyFetch>
     */
    public function resolveAssignsToLocalPropertyFetches(FunctionLike $functionLike) : array
    {
        return $this->betterNodeFinder->find((array) $functionLike->getStmts(), function (Node $node) : bool {
            if (!$this->propertyFetchAnalyzer->isLocalPropertyFetch($node)) {
                return \false;
            }
            return $this->contextAnalyzer->isLeftPartOfAssign($node);
        });
    }
}
