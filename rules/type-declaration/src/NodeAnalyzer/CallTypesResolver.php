<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\NodeCollector\ValueObject\ArrayCallable;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;

final class CallTypesResolver
{
    /**
     * @var string
     */
    private const STRICTNESS_TYPE_DECLARATION = 'type_declaration';

    /**
     * @var string
     */
    private const STRICTNESS_DOCBLOCK = 'docblock';

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    public function __construct(NodeTypeResolver $nodeTypeResolver, TypeFactory $typeFactory)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->typeFactory = $typeFactory;
    }

    /**
     * @param MethodCall[]|StaticCall[]|ArrayCallable[] $calls
     * @return Type[]
     */
    public function resolveStrictTypesFromCalls(array $calls): array
    {
        return $this->resolveTypesFromCalls($calls, self::STRICTNESS_TYPE_DECLARATION);
    }

    /**
     * @param MethodCall[]|StaticCall[]|ArrayCallable[] $calls
     * @return Type[]
     */
    public function resolveWeakTypesFromCalls(array $calls): array
    {
        return $this->resolveTypesFromCalls($calls, self::STRICTNESS_DOCBLOCK);
    }

    /**
     * @param MethodCall[]|StaticCall[]|ArrayCallable[] $calls
     * @return Type[]
     */
    private function resolveTypesFromCalls(array $calls, string $strictnessLevel): array
    {
        $staticTypesByArgumentPosition = [];

        foreach ($calls as $call) {
            if (! $call instanceof StaticCall && ! $call instanceof MethodCall) {
                continue;
            }

            foreach ($call->args as $position => $arg) {
                $argValueType = $this->resolveArgValueType($strictnessLevel, $arg);

                dump($argValueType);

                $staticTypesByArgumentPosition[$position][] = $argValueType;
            }
        }

        // unite to single type
        return $this->unionToSingleType($staticTypesByArgumentPosition);
    }

    private function resolveArgValueType(string $strictnessLevel, Arg $arg): Type
    {
        if ($strictnessLevel === self::STRICTNESS_TYPE_DECLARATION) {
            $argValueType = $this->nodeTypeResolver->getNativeType($arg->value);
        } else {
            $argValueType = $this->nodeTypeResolver->resolve($arg->value);
        }

        // "self" in another object is not correct, this make it independent
        return $this->correctSelfType($argValueType);
    }

    private function correctSelfType(Type $argValueType): Type
    {
        if ($argValueType instanceof ThisType) {
            return new ObjectType($argValueType->getClassName());
        }

        return $argValueType;
    }

    /**
     * @param array<int, Type[]> $staticTypesByArgumentPosition
     * @return array<int, Type>
     */
    private function unionToSingleType(array $staticTypesByArgumentPosition): array
    {
        $staticTypeByArgumentPosition = [];
        foreach ($staticTypesByArgumentPosition as $position => $staticTypes) {
            $unionedType = $this->typeFactory->createMixedPassedOrUnionType($staticTypes);

            // narrow parents to most child type
            $unionedType = $this->narrowParentObjectTreeToSingleObjectChildType($unionedType);

            $staticTypeByArgumentPosition[$position] = $unionedType;
        }

        return $staticTypeByArgumentPosition;
    }

    private function narrowParentObjectTreeToSingleObjectChildType(Type $type): Type
    {
        if (! $type instanceof UnionType) {
            return $type;
        }

        $firstUnionedType = null;

        foreach ($type->getTypes() as $unionedType) {
            if (! $unionedType instanceof TypeWithClassName) {
                return $type;
            }

            if ($firstUnionedType === null) {
                $firstUnionedType = $unionedType;
            }
        }

        foreach ($type->getTypes() as $unionedType) {
            if (! $unionedType instanceof TypeWithClassName) {
                return $type;
            }

            if (! is_a($firstUnionedType->getClassName(), $unionedType->getClassName(), true)) {
                return $type;
            }
        }

        return $firstUnionedType;
    }
}
