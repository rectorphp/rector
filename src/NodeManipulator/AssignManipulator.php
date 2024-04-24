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
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php72\ValueObject\ListAndEach;
use Rector\PhpParser\Node\BetterNodeFinder;
final class AssignManipulator
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    public function __construct(NodeNameResolver $nodeNameResolver, BetterNodeFinder $betterNodeFinder, PropertyFetchAnalyzer $propertyFetchAnalyzer)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
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
    public function isLeftPartOfAssign(Node $node) : bool
    {
        if ($node->getAttribute(AttributeKey::IS_BEING_ASSIGNED) === \true) {
            return \true;
        }
        return $node->getAttribute(AttributeKey::IS_ASSIGNED_TO) === \true;
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
            return $this->isLeftPartOfAssign($node);
        });
    }
}
