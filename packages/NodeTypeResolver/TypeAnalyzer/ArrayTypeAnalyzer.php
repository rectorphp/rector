<?php

declare(strict_types=1);

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
    public function __construct(
        private readonly NodeNameResolver $nodeNameResolver,
        private readonly NodeTypeResolver $nodeTypeResolver,
        private readonly PregMatchTypeCorrector $pregMatchTypeCorrector,
        private readonly BetterNodeFinder $betterNodeFinder,
    ) {
    }

    public function isArrayType(Node $node): bool
    {
        $nodeStaticType = $this->nodeTypeResolver->getType($node);

        $nodeStaticType = $this->pregMatchTypeCorrector->correct($node, $nodeStaticType);
        if ($this->isIntersectionArrayType($nodeStaticType)) {
            return true;
        }

        // PHPStan false positive, when variable has type[] docblock, but default array is missing
        if (($node instanceof PropertyFetch || $node instanceof StaticPropertyFetch) && ! $this->isPropertyFetchWithArrayDefault(
            $node
        )) {
            return false;
        }

        if ($nodeStaticType instanceof MixedType) {
            if ($nodeStaticType->isExplicitMixed()) {
                return false;
            }

            if ($this->isPropertyFetchWithArrayDefault($node)) {
                return true;
            }
        }

        return $nodeStaticType instanceof ArrayType;
    }

    private function isIntersectionArrayType(Type $nodeType): bool
    {
        if (! $nodeType instanceof IntersectionType) {
            return false;
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

            return false;
        }

        return true;
    }

    /**
     * phpstan bug workaround - https://phpstan.org/r/0443f283-244c-42b8-8373-85e7deb3504c
     */
    private function isPropertyFetchWithArrayDefault(Node $node): bool
    {
        if (! $node instanceof PropertyFetch && ! $node instanceof StaticPropertyFetch) {
            return false;
        }

        $classLike = $this->betterNodeFinder->findParentType($node, ClassLike::class);
        if ($classLike instanceof Interface_) {
            return false;
        }

        if (! $classLike instanceof ClassLike) {
            return false;
        }

        $propertyName = $this->nodeNameResolver->getName($node->name);
        if ($propertyName === null) {
            return false;
        }

        $property = $classLike->getProperty($propertyName);
        if ($property !== null) {
            $propertyProperty = $property->props[0];
            return $propertyProperty->default instanceof Array_;
        }

        // also possible 3rd party vendor
        if ($node instanceof PropertyFetch) {
            $propertyOwnerStaticType = $this->nodeTypeResolver->getType($node->var);
        } else {
            $propertyOwnerStaticType = $this->nodeTypeResolver->getType($node->class);
        }

        if ($propertyOwnerStaticType instanceof ThisType) {
            return false;
        }

        return $propertyOwnerStaticType instanceof TypeWithClassName;
    }
}
