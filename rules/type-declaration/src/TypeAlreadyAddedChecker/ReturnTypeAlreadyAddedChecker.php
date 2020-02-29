<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeAlreadyAddedChecker;

use Iterator;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\IterableType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\StaticTypeMapper;

final class ReturnTypeAlreadyAddedChecker
{
    /**
     * @var string[]
     */
    private const FOREACHABLE_TYPES = ['iterable', 'Iterator', 'Traversable', 'array'];

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(
        StaticTypeMapper $staticTypeMapper,
        BetterStandardPrinter $betterStandardPrinter,
        NodeNameResolver $nodeNameResolver
    ) {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * @param ClassMethod|Function_ $node
     */
    public function isReturnTypeAlreadyAdded(Node $node, Type $returnType): bool
    {
        $returnNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($returnType);
        if ($node->returnType === null) {
            return false;
        }

        if ($this->betterStandardPrinter->areNodesEqual($node->returnType, $returnNode)) {
            return true;
        }

        // is array <=> iterable <=> Iterator co-type? → skip
        if ($this->isArrayIterableIteratorCoType($node, $returnType)) {
            return true;
        }

        // is class-string<T> type? → skip
        if ($returnType instanceof GenericObjectType && $returnType->getClassName() === 'class-string') {
            return true;
        }

        // prevent overriding self with itself
        if ($this->betterStandardPrinter->printWithoutComments($node->returnType) === 'self') {
            $className = $node->getAttribute(AttributeKey::CLASS_NAME);
            if (ltrim($this->betterStandardPrinter->printWithoutComments($returnNode), '\\') === $className) {
                return true;
            }
        }

        return false;
    }

    private function isArrayIterableIteratorCoType(Node $node, Type $returnType): bool
    {
        if (! $this->nodeNameResolver->isNames($node->returnType, self::FOREACHABLE_TYPES)) {
            return false;
        }

        return $this->isStaticTypeIterable($returnType);
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

        return $type instanceof ObjectType && $type->getClassName() === Iterator::class;
    }
}
