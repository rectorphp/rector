<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\NodeCollector\ValueObject\ArrayCallable;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\TypeDeclaration\Enum\TypeStrictness;

final class CallTypesResolver
{
    public function __construct(
        private NodeTypeResolver $nodeTypeResolver,
        private TypeFactory $typeFactory
    ) {
    }

    /**
     * @param MethodCall[]|StaticCall[]|ArrayCallable[] $calls
     * @return Type[]
     */
    public function resolveStrictTypesFromCalls(array $calls): array
    {
        return $this->resolveTypesFromCalls($calls, TypeStrictness::STRICTNESS_TYPE_DECLARATION());
    }

    /**
     * @param MethodCall[]|StaticCall[]|ArrayCallable[] $calls
     * @return Type[]
     */
    public function resolveWeakTypesFromCalls(array $calls): array
    {
        return $this->resolveTypesFromCalls($calls, TypeStrictness::STRICTNESS_DOCBLOCK());
    }

    /**
     * @param MethodCall[]|StaticCall[]|ArrayCallable[] $calls
     * @return Type[]
     */
    private function resolveTypesFromCalls(array $calls, TypeStrictness $typeStrictness): array
    {
        $staticTypesByArgumentPosition = [];

        foreach ($calls as $call) {
            if (! $call instanceof StaticCall && ! $call instanceof MethodCall) {
                continue;
            }

            foreach ($call->args as $position => $arg) {
                $argValueType = $this->resolveArgValueType($typeStrictness, $arg);
                $staticTypesByArgumentPosition[$position][] = $argValueType;
            }
        }

        // unite to single type
        return $this->unionToSingleType($staticTypesByArgumentPosition);
    }

    private function resolveArgValueType(TypeStrictness $typeStrictness, Arg $arg): Type
    {
        if ($typeStrictness->equals(TypeStrictness::STRICTNESS_TYPE_DECLARATION())) {
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
        if (count($staticTypeByArgumentPosition) !== 1) {
            return $staticTypeByArgumentPosition;
        }
        if (! $staticTypeByArgumentPosition[0] instanceof NullType) {
            return $staticTypeByArgumentPosition;
        }
        return [new MixedType()];
    }

    private function narrowParentObjectTreeToSingleObjectChildType(Type $type): Type
    {
        if (! $type instanceof UnionType) {
            return $type;
        }

        if (! $this->isTypeWithClassNameOnly($type)) {
            return $type;
        }

        /** @var TypeWithClassName $firstUnionedType */
        $firstUnionedType = $type->getTypes()[0];

        foreach ($type->getTypes() as $unionedType) {
            if (! $unionedType instanceof TypeWithClassName) {
                return $type;
            }

            if ($unionedType->isSuperTypeOf($firstUnionedType)->yes()) {
                return $type;
            }
        }

        return $firstUnionedType;
    }

    private function isTypeWithClassNameOnly(UnionType $unionType): bool
    {
        foreach ($unionType->getTypes() as $unionedType) {
            if (! $unionedType instanceof TypeWithClassName) {
                return false;
            }
        }

        return true;
    }
}
