<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\TypeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use PHPStan\Type\Accessory\HasOffsetType;
use PHPStan\Type\Accessory\NonEmptyArrayType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeCorrector\PregMatchTypeCorrector;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class ArrayTypeAnalyzer
{
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeCorrector\PregMatchTypeCorrector
     */
    private $pregMatchTypeCorrector;
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\NodeTypeResolver\NodeTypeCorrector\PregMatchTypeCorrector $pregMatchTypeCorrector, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->pregMatchTypeCorrector = $pregMatchTypeCorrector;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function isArrayType(\PhpParser\Node $node) : bool
    {
        $nodeStaticType = $this->nodeTypeResolver->getType($node);
        $nodeStaticType = $this->pregMatchTypeCorrector->correct($node, $nodeStaticType);
        if ($this->isIntersectionArrayType($nodeStaticType)) {
            return \true;
        }
        // PHPStan false positive, when variable has type[] docblock, but default array is missing
        if (($node instanceof \PhpParser\Node\Expr\PropertyFetch || $node instanceof \PhpParser\Node\Expr\StaticPropertyFetch) && !$this->isPropertyFetchWithArrayDefault($node)) {
            return \false;
        }
        if ($nodeStaticType instanceof \PHPStan\Type\MixedType) {
            if ($nodeStaticType->isExplicitMixed()) {
                return \false;
            }
            if ($this->isPropertyFetchWithArrayDefault($node)) {
                return \true;
            }
        }
        return $nodeStaticType instanceof \PHPStan\Type\ArrayType;
    }
    private function isIntersectionArrayType(\PHPStan\Type\Type $nodeType) : bool
    {
        if (!$nodeType instanceof \PHPStan\Type\IntersectionType) {
            return \false;
        }
        foreach ($nodeType->getTypes() as $intersectionNodeType) {
            if ($intersectionNodeType instanceof \PHPStan\Type\ArrayType) {
                continue;
            }
            if ($intersectionNodeType instanceof \PHPStan\Type\Accessory\HasOffsetType) {
                continue;
            }
            if ($intersectionNodeType instanceof \PHPStan\Type\Accessory\NonEmptyArrayType) {
                continue;
            }
            return \false;
        }
        return \true;
    }
    /**
     * phpstan bug workaround - https://phpstan.org/r/0443f283-244c-42b8-8373-85e7deb3504c
     */
    private function isPropertyFetchWithArrayDefault(\PhpParser\Node $node) : bool
    {
        if (!$node instanceof \PhpParser\Node\Expr\PropertyFetch && !$node instanceof \PhpParser\Node\Expr\StaticPropertyFetch) {
            return \false;
        }
        $classLike = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\ClassLike::class);
        if ($classLike instanceof \PhpParser\Node\Stmt\Interface_) {
            return \false;
        }
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return \false;
        }
        $propertyName = $this->nodeNameResolver->getName($node->name);
        if ($propertyName === null) {
            return \false;
        }
        $property = $classLike->getProperty($propertyName);
        if ($property !== null) {
            $propertyProperty = $property->props[0];
            return $propertyProperty->default instanceof \PhpParser\Node\Expr\Array_;
        }
        // also possible 3rd party vendor
        if ($node instanceof \PhpParser\Node\Expr\PropertyFetch) {
            $propertyOwnerStaticType = $this->nodeTypeResolver->getType($node->var);
        } else {
            $propertyOwnerStaticType = $this->nodeTypeResolver->getType($node->class);
        }
        if ($propertyOwnerStaticType instanceof \PHPStan\Type\ThisType) {
            return \false;
        }
        return $propertyOwnerStaticType instanceof \PHPStan\Type\TypeWithClassName;
    }
}
