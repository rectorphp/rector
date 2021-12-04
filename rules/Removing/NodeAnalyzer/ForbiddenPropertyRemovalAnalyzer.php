<?php

declare (strict_types=1);
namespace Rector\Removing\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Clone_;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ThisType;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class ForbiddenPropertyRemovalAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer $propertyFetchAnalyzer)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeComparator = $nodeComparator;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
    }
    public function isForbiddenInNewCurrentClassNameSelfClone(string $propertyName, ?\PhpParser\Node\Stmt\ClassLike $classLike) : bool
    {
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return \false;
        }
        $methods = $classLike->getMethods();
        foreach ($methods as $method) {
            $isInNewCurrentClassNameSelfClone = (bool) $this->betterNodeFinder->findFirst((array) $method->getStmts(), function (\PhpParser\Node $subNode) use($classLike, $propertyName) : bool {
                if ($subNode instanceof \PhpParser\Node\Expr\New_) {
                    return $this->isPropertyNameUsedAfterNewOrClone($subNode, $classLike, $propertyName);
                }
                if ($subNode instanceof \PhpParser\Node\Expr\Clone_) {
                    return $this->isPropertyNameUsedAfterNewOrClone($subNode, $classLike, $propertyName);
                }
                return \false;
            });
            if ($isInNewCurrentClassNameSelfClone) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param \PhpParser\Node\Expr\Clone_|\PhpParser\Node\Expr\New_ $expr
     */
    private function isPropertyNameUsedAfterNewOrClone($expr, \PhpParser\Node\Stmt\ClassLike $classLike, string $propertyName) : bool
    {
        $parentAssign = $this->betterNodeFinder->findParentType($expr, \PhpParser\Node\Expr\Assign::class);
        if (!$parentAssign instanceof \PhpParser\Node\Expr\Assign) {
            return \false;
        }
        $className = (string) $this->nodeNameResolver->getName($classLike);
        $type = $expr instanceof \PhpParser\Node\Expr\New_ ? $this->nodeTypeResolver->getType($expr->class) : $this->nodeTypeResolver->getType($expr->expr);
        if ($expr instanceof \PhpParser\Node\Expr\Clone_ && $type instanceof \PHPStan\Type\ThisType) {
            $type = $type->getStaticObjectType();
        }
        if ($type instanceof \PHPStan\Type\ObjectType) {
            return $this->isFoundAfterCloneOrNew($type, $expr, $parentAssign, $className, $propertyName);
        }
        return \false;
    }
    /**
     * @param \PhpParser\Node\Expr\Clone_|\PhpParser\Node\Expr\New_ $expr
     */
    private function isFoundAfterCloneOrNew(\PHPStan\Type\ObjectType $objectType, $expr, \PhpParser\Node\Expr\Assign $parentAssign, string $className, string $propertyName) : bool
    {
        if ($objectType->getClassName() !== $className) {
            return \false;
        }
        return (bool) $this->betterNodeFinder->findFirstNext($expr, function (\PhpParser\Node $subNode) use($parentAssign, $propertyName) : bool {
            if (!$this->propertyFetchAnalyzer->isPropertyFetch($subNode)) {
                return \false;
            }
            /** @var PropertyFetch|StaticPropertyFetch $subNode */
            $propertyFetchName = (string) $this->nodeNameResolver->getName($subNode);
            if ($subNode instanceof \PhpParser\Node\Expr\PropertyFetch) {
                if (!$this->nodeComparator->areNodesEqual($subNode->var, $parentAssign->var)) {
                    return \false;
                }
                return $propertyFetchName === $propertyName;
            }
            if (!$this->nodeComparator->areNodesEqual($subNode->class, $parentAssign->var)) {
                return \false;
            }
            return $propertyFetchName === $propertyName;
        });
    }
}
