<?php

declare (strict_types=1);
namespace Rector\Nette\NodeAdding;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNestingScope\ParentScopeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class FunctionLikeFirstLevelStatementResolver
{
    /**
     * @var \Rector\NodeNestingScope\ParentScopeFinder
     */
    private $parentScopeFinder;
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\NodeNestingScope\ParentScopeFinder $parentScopeFinder, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->parentScopeFinder = $parentScopeFinder;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function resolveFirstLevelStatement(\PhpParser\Node $node) : \PhpParser\Node
    {
        $multiplierClosure = $this->matchMultiplierClosure($node);
        /** @var ClassMethod|Closure|null $functionLike */
        $functionLike = $multiplierClosure ?? $this->parentScopeFinder->find($node);
        if ($functionLike === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $currentStatement = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT);
        if (!$currentStatement instanceof \PhpParser\Node) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        while (!\in_array($currentStatement, (array) $functionLike->stmts, \true)) {
            $parent = $currentStatement->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            if (!$parent instanceof \PhpParser\Node) {
                throw new \Rector\Core\Exception\ShouldNotHappenException();
            }
            $currentStatement = $parent->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT);
            if (!$currentStatement instanceof \PhpParser\Node) {
                throw new \Rector\Core\Exception\ShouldNotHappenException();
            }
        }
        return $currentStatement;
    }
    /**
     * Form might be costructured inside private closure for multiplier
     *
     * @see https://doc.nette.org/en/3.0/multiplier
     */
    private function matchMultiplierClosure(\PhpParser\Node $node) : ?\PhpParser\Node\Expr\Closure
    {
        $closure = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Expr\Closure::class);
        if (!$closure instanceof \PhpParser\Node\Expr\Closure) {
            return null;
        }
        $parent = $closure->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parent instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $parentParent = $parent->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentParent instanceof \PhpParser\Node\Expr\New_) {
            return null;
        }
        return $closure;
    }
}
