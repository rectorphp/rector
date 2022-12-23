<?php

declare (strict_types=1);
namespace Rector\Core\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\PostDec;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PreDec;
use PhpParser\Node\Expr\PreInc;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Util\MultiInstanceofChecker;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class AssignManipulator
{
    /**
     * @var array<class-string<Expr>>
     */
    private const MODIFYING_NODE_TYPES = [AssignOp::class, PreDec::class, PostDec::class, PreInc::class, PostInc::class];
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\Util\MultiInstanceofChecker
     */
    private $multiInstanceofChecker;
    public function __construct(NodeNameResolver $nodeNameResolver, NodeComparator $nodeComparator, BetterNodeFinder $betterNodeFinder, PropertyFetchAnalyzer $propertyFetchAnalyzer, MultiInstanceofChecker $multiInstanceofChecker)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeComparator = $nodeComparator;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->multiInstanceofChecker = $multiInstanceofChecker;
    }
    /**
     * Matches:
     * each() = [1, 2];
     */
    public function isListToEachAssign(Assign $assign) : bool
    {
        if (!$assign->expr instanceof FuncCall) {
            return \false;
        }
        if (!$assign->var instanceof List_) {
            return \false;
        }
        return $this->nodeNameResolver->isName($assign->expr, 'each');
    }
    public function isLeftPartOfAssign(Node $node) : bool
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Assign && $this->nodeComparator->areNodesEqual($parentNode->var, $node)) {
            return \true;
        }
        if ($parentNode !== null && $this->multiInstanceofChecker->isInstanceOf($parentNode, self::MODIFYING_NODE_TYPES)) {
            return \true;
        }
        if ($this->isOnArrayDestructuring($parentNode)) {
            return \true;
        }
        // traverse up to array dim fetches
        if ($parentNode instanceof ArrayDimFetch) {
            $previousParent = $parentNode;
            while ($parentNode instanceof ArrayDimFetch) {
                $previousParent = $parentNode;
                $parentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
            }
            if ($parentNode instanceof Assign) {
                return $parentNode->var === $previousParent;
            }
        }
        return \false;
    }
    public function isNodePartOfAssign(Node $node) : bool
    {
        $previousNode = $node;
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        while ($parentNode instanceof Node && !$parentNode instanceof Expression) {
            if ($parentNode instanceof Assign && $this->nodeComparator->areNodesEqual($parentNode->var, $previousNode)) {
                return \true;
            }
            $previousNode = $parentNode;
            $parentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
        }
        return \false;
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
    private function isOnArrayDestructuring(?Node $parentNode) : bool
    {
        if (!$parentNode instanceof ArrayItem) {
            return \false;
        }
        $parentArrayItem = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentArrayItem instanceof Array_) {
            return \false;
        }
        $node = $parentArrayItem->getAttribute(AttributeKey::PARENT_NODE);
        return $node instanceof Assign && $node->var === $parentArrayItem;
    }
}
