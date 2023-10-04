<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\TypeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Type\Accessory\HasOffsetType;
use PHPStan\Type\Accessory\NonEmptyArrayType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class ArrayTypeAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(NodeTypeResolver $nodeTypeResolver, ReflectionResolver $reflectionResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function isArrayType(Expr $expr) : bool
    {
        $nodeType = $this->nodeTypeResolver->getNativeType($expr);
        if ($this->isIntersectionArrayType($nodeType)) {
            return \true;
        }
        // PHPStan false positive, when variable has type[] docblock, but default array is missing
        if (($expr instanceof PropertyFetch || $expr instanceof StaticPropertyFetch) && $this->isPropertyFetchWithArrayDefault($expr)) {
            return \true;
        }
        if ($nodeType instanceof MixedType) {
            if ($nodeType->isExplicitMixed()) {
                return \false;
            }
            if ($this->isPropertyFetchWithArrayDefault($expr)) {
                return \true;
            }
        }
        return $nodeType instanceof ArrayType;
    }
    private function isIntersectionArrayType(Type $nodeType) : bool
    {
        if (!$nodeType instanceof IntersectionType) {
            return \false;
        }
        foreach ($nodeType->getTypes() as $intersectionNodeType) {
            if ($intersectionNodeType instanceof ArrayType) {
                continue;
            }
            if ($intersectionNodeType instanceof HasOffsetType) {
                continue;
            }
            if ($intersectionNodeType instanceof NonEmptyArrayType) {
                continue;
            }
            return \false;
        }
        return \true;
    }
    /**
     * phpstan bug workaround - https://phpstan.org/r/0443f283-244c-42b8-8373-85e7deb3504c
     */
    private function isPropertyFetchWithArrayDefault(Expr $expr) : bool
    {
        if (!$expr instanceof PropertyFetch && !$expr instanceof StaticPropertyFetch) {
            return \false;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($expr);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        $phpPropertyReflection = $this->reflectionResolver->resolvePropertyReflectionFromPropertyFetch($expr);
        if ($phpPropertyReflection instanceof PhpPropertyReflection) {
            $reflectionProperty = $phpPropertyReflection->getNativeReflection();
            $betterReflection = $reflectionProperty->getBetterReflection();
            $defaultValueExpr = $betterReflection->getDefaultValueExpression();
            return $defaultValueExpr instanceof Array_;
        }
        return \false;
    }
}
