<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
final class CallTypesResolver
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeComparator\TypeComparator
     */
    private $typeComparator;
    public function __construct(NodeTypeResolver $nodeTypeResolver, TypeFactory $typeFactory, ReflectionProvider $reflectionProvider, TypeComparator $typeComparator)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->typeFactory = $typeFactory;
        $this->reflectionProvider = $reflectionProvider;
        $this->typeComparator = $typeComparator;
    }
    /**
     * @param MethodCall[]|StaticCall[] $calls
     * @return array<int, Type>
     */
    public function resolveStrictTypesFromCalls(array $calls) : array
    {
        $staticTypesByArgumentPosition = [];
        foreach ($calls as $call) {
            foreach ($call->args as $position => $arg) {
                // there is first class callable usage, or argument unpack, or named arg
                // simply returns array marks as unknown as can be anything and in any position
                if (!$arg instanceof Arg || $arg->unpack || $arg->name instanceof Identifier) {
                    return [];
                }
                $staticTypesByArgumentPosition[$position][] = $this->resolveStrictArgValueType($arg);
            }
        }
        // unite to single type
        return $this->unionToSingleType($staticTypesByArgumentPosition);
    }
    private function resolveStrictArgValueType(Arg $arg) : Type
    {
        $argValueType = $this->nodeTypeResolver->getNativeType($arg->value);
        // "self" in another object is not correct, this make it independent
        $argValueType = $this->correctSelfType($argValueType);
        if (!$argValueType instanceof ObjectType) {
            return $argValueType;
        }
        // fix false positive generic type on string
        if (!$this->reflectionProvider->hasClass($argValueType->getClassName())) {
            return new MixedType();
        }
        $type = $this->nodeTypeResolver->getType($arg->value);
        if (!$type->equals($argValueType) && $this->typeComparator->isSubtype($type, $argValueType)) {
            return $type;
        }
        return $argValueType;
    }
    private function correctSelfType(Type $argValueType) : Type
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
    private function unionToSingleType(array $staticTypesByArgumentPosition) : array
    {
        $staticTypeByArgumentPosition = [];
        foreach ($staticTypesByArgumentPosition as $position => $staticTypes) {
            $unionedType = $this->typeFactory->createMixedPassedOrUnionType($staticTypes);
            // narrow parents to most child type
            $staticTypeByArgumentPosition[$position] = $this->narrowParentObjectTreeToSingleObjectChildType($unionedType);
        }
        if (\count($staticTypeByArgumentPosition) !== 1) {
            return $staticTypeByArgumentPosition;
        }
        if (!$staticTypeByArgumentPosition[0] instanceof NullType) {
            return $staticTypeByArgumentPosition;
        }
        return [new MixedType()];
    }
    private function narrowParentObjectTreeToSingleObjectChildType(Type $type) : Type
    {
        if (!$type instanceof UnionType) {
            return $type;
        }
        if (!$this->isTypeWithClassNameOnly($type)) {
            return $type;
        }
        /** @var TypeWithClassName $firstUnionedType */
        $firstUnionedType = $type->getTypes()[0];
        foreach ($type->getTypes() as $unionedType) {
            if (!$unionedType instanceof TypeWithClassName) {
                return $type;
            }
            if ($unionedType->isSuperTypeOf($firstUnionedType)->yes()) {
                return $type;
            }
        }
        return $firstUnionedType;
    }
    private function isTypeWithClassNameOnly(UnionType $unionType) : bool
    {
        foreach ($unionType->getTypes() as $unionedType) {
            if (!$unionedType instanceof TypeWithClassName) {
                return \false;
            }
        }
        return \true;
    }
}
