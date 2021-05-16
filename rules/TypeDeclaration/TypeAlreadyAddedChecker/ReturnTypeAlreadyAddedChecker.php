<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeAlreadyAddedChecker;

use Iterator;
use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\UnionType as PhpParserUnionType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\IterableType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Traversable;

final class ReturnTypeAlreadyAddedChecker
{
    /**
     * @var string[]|class-string<Traversable>[]
     */
    private const FOREACHABLE_TYPES = ['iterable', 'Iterator', 'Traversable', 'array'];

    public function __construct(
        private NodeNameResolver $nodeNameResolver,
        private StaticTypeMapper $staticTypeMapper,
        private NodeComparator $nodeComparator
    ) {
    }

    /**
     * @param ClassMethod|Function_ $functionLike
     */
    public function isSameOrBetterReturnTypeAlreadyAdded(FunctionLike $functionLike, Type $returnType): bool
    {
        $nodeReturnType = $functionLike->returnType;

        /** @param Identifier|Name|NullableType|PhpParserUnionType|null $returnTypeNode */
        if ($nodeReturnType === null) {
            return false;
        }

        $returnNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($returnType);
        if ($this->nodeComparator->areNodesEqual($nodeReturnType, $returnNode)) {
            return true;
        }

        // is array <=> iterable <=> Iterator co-type? → skip
        if ($this->isArrayIterableIteratorCoType($nodeReturnType, $returnType)) {
            return true;
        }

        if ($this->isUnionCoType($nodeReturnType, $returnType)) {
            return true;
        }

        // is class-string<T> type? → skip
        if ($returnType instanceof GenericObjectType && $returnType->getClassName() === 'class-string') {
            return true;
        }

        // prevent overriding self with itself
        if (! $functionLike->returnType instanceof Name) {
            return false;
        }

        if ($functionLike->returnType->toLowerString() !== 'self') {
            return false;
        }

        // skip "self" by "static" override
        if ($returnType instanceof ThisType) {
            return true;
        }

        $className = $functionLike->getAttribute(AttributeKey::CLASS_NAME);

        $nodeContent = $this->nodeComparator->printWithoutComments($returnNode);
        $nodeContentWithoutPreslash = ltrim($nodeContent, '\\');

        return $nodeContentWithoutPreslash === $className;
    }

    /**
     * @param Identifier|Name|NullableType|PhpParserUnionType $returnTypeNode
     */
    private function isArrayIterableIteratorCoType(Node $returnTypeNode, Type $returnType): bool
    {
        if (! $this->nodeNameResolver->isNames($returnTypeNode, self::FOREACHABLE_TYPES)) {
            return false;
        }

        return $this->isStaticTypeIterable($returnType);
    }

    /**
     * @param Identifier|Name|NullableType|PhpParserUnionType $returnTypeNode
     */
    private function isUnionCoType(Node $returnTypeNode, Type $type): bool
    {
        if (! $type instanceof UnionType) {
            return false;
        }

        // skip nullable type
        $nullType = new NullType();
        if ($type->isSuperTypeOf($nullType)->yes()) {
            return false;
        }

        $classMethodReturnType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($returnTypeNode);
        return $type->isSuperTypeOf($classMethodReturnType)
            ->yes();
    }

    private function isStaticTypeIterable(Type $type): bool
    {
        if ($this->isArrayIterableOrIteratorType($type)) {
            return true;
        }

        if ($type instanceof UnionType || $type instanceof IntersectionType) {
            foreach ($type->getTypes() as $joinedType) {
                if (! $this->isStaticTypeIterable($joinedType)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    private function isArrayIterableOrIteratorType(Type $type): bool
    {
        if ($type instanceof ArrayType) {
            return true;
        }

        if ($type instanceof IterableType) {
            return true;
        }
        if (! $type instanceof ObjectType) {
            return false;
        }
        return $type->getClassName() === Iterator::class;
    }
}
