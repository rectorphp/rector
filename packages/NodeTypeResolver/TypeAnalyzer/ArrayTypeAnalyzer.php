<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\TypeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Type\Accessory\HasOffsetType;
use PHPStan\Type\Accessory\NonEmptyArrayType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeCorrector\PregMatchTypeCorrector;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class ArrayTypeAnalyzer
{
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
     * @var \Rector\NodeTypeResolver\NodeTypeCorrector\PregMatchTypeCorrector
     */
    private $pregMatchTypeCorrector;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\NodeTypeResolver\NodeTypeCorrector\PregMatchTypeCorrector $pregMatchTypeCorrector, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->pregMatchTypeCorrector = $pregMatchTypeCorrector;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function isArrayType(\PhpParser\Node $node) : bool
    {
        $nodeType = $this->nodeTypeResolver->getType($node);
        $nodeType = $this->pregMatchTypeCorrector->correct($node, $nodeType);
        if ($this->isIntersectionArrayType($nodeType)) {
            return \true;
        }
        // PHPStan false positive, when variable has type[] docblock, but default array is missing
        if ($node instanceof \PhpParser\Node\Expr\PropertyFetch || $node instanceof \PhpParser\Node\Expr\StaticPropertyFetch) {
            if ($this->isPropertyFetchWithArrayDefault($node)) {
                return \true;
            }
            if ($this->isPropertyFetchWithArrayDocblockWithoutDefault($node)) {
                return \false;
            }
        }
        if ($nodeType instanceof \PHPStan\Type\MixedType) {
            if ($nodeType->isExplicitMixed()) {
                return \false;
            }
            if ($this->isPropertyFetchWithArrayDefault($node)) {
                return \true;
            }
        }
        return $nodeType instanceof \PHPStan\Type\ArrayType;
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
    private function isPropertyFetchWithArrayDocblockWithoutDefault(\PhpParser\Node $node) : bool
    {
        if (!$node instanceof \PhpParser\Node\Expr\PropertyFetch && !$node instanceof \PhpParser\Node\Expr\StaticPropertyFetch) {
            return \false;
        }
        $classLike = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\ClassLike::class);
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return \false;
        }
        $propertyName = $this->nodeNameResolver->getName($node->name);
        if ($propertyName === null) {
            return \false;
        }
        $property = $classLike->getProperty($propertyName);
        if (!$property instanceof \PhpParser\Node\Stmt\Property) {
            return \false;
        }
        $propertyProperty = $property->props[0];
        if ($propertyProperty->default instanceof \PhpParser\Node\Expr\Array_) {
            return \false;
        }
        $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNode($property);
        if (!$propertyPhpDocInfo instanceof \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
            return \false;
        }
        $varType = $propertyPhpDocInfo->getVarType();
        return $varType instanceof \PHPStan\Type\ArrayType || $varType instanceof \PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode || $varType instanceof \PHPStan\Type\IterableType;
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
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return \false;
        }
        $propertyName = $this->nodeNameResolver->getName($node->name);
        if ($propertyName === null) {
            return \false;
        }
        // A. local property
        $property = $classLike->getProperty($propertyName);
        if ($property !== null) {
            $propertyProperty = $property->props[0];
            return $propertyProperty->default instanceof \PhpParser\Node\Expr\Array_;
        }
        // B. another object property
        $phpPropertyReflection = $this->reflectionResolver->resolvePropertyReflectionFromPropertyFetch($node);
        if ($phpPropertyReflection instanceof \PHPStan\Reflection\Php\PhpPropertyReflection) {
            $reflectionProperty = $phpPropertyReflection->getNativeReflection();
            return \is_array($reflectionProperty->getDefaultValue());
        }
        return \false;
    }
}
