<?php

declare (strict_types=1);
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
    public function __construct(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory $typeFactory)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->typeFactory = $typeFactory;
    }
    /**
     * @param MethodCall[]|StaticCall[]|ArrayCallable[] $calls
     * @return array<int, Type>
     */
    public function resolveStrictTypesFromCalls(array $calls) : array
    {
        $staticTypesByArgumentPosition = [];
        foreach ($calls as $call) {
            if (!$call instanceof \PhpParser\Node\Expr\StaticCall && !$call instanceof \PhpParser\Node\Expr\MethodCall) {
                continue;
            }
            foreach ($call->args as $position => $arg) {
                if (!$arg instanceof \PhpParser\Node\Arg) {
                    continue;
                }
                $argValueType = $this->resolveStrictArgValueType($arg);
                $staticTypesByArgumentPosition[$position][] = $argValueType;
            }
        }
        // unite to single type
        return $this->unionToSingleType($staticTypesByArgumentPosition);
    }
    private function resolveStrictArgValueType(\PhpParser\Node\Arg $arg) : \PHPStan\Type\Type
    {
        $argValueType = $this->nodeTypeResolver->getNativeType($arg->value);
        // "self" in another object is not correct, this make it independent
        return $this->correctSelfType($argValueType);
    }
    private function correctSelfType(\PHPStan\Type\Type $argValueType) : \PHPStan\Type\Type
    {
        if ($argValueType instanceof \PHPStan\Type\ThisType) {
            return new \PHPStan\Type\ObjectType($argValueType->getClassName());
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
            $unionedType = $this->narrowParentObjectTreeToSingleObjectChildType($unionedType);
            $staticTypeByArgumentPosition[$position] = $unionedType;
        }
        if (\count($staticTypeByArgumentPosition) !== 1) {
            return $staticTypeByArgumentPosition;
        }
        if (!$staticTypeByArgumentPosition[0] instanceof \PHPStan\Type\NullType) {
            return $staticTypeByArgumentPosition;
        }
        return [new \PHPStan\Type\MixedType()];
    }
    private function narrowParentObjectTreeToSingleObjectChildType(\PHPStan\Type\Type $type) : \PHPStan\Type\Type
    {
        if (!$type instanceof \PHPStan\Type\UnionType) {
            return $type;
        }
        if (!$this->isTypeWithClassNameOnly($type)) {
            return $type;
        }
        /** @var TypeWithClassName $firstUnionedType */
        $firstUnionedType = $type->getTypes()[0];
        foreach ($type->getTypes() as $unionedType) {
            if (!$unionedType instanceof \PHPStan\Type\TypeWithClassName) {
                return $type;
            }
            if ($unionedType->isSuperTypeOf($firstUnionedType)->yes()) {
                return $type;
            }
        }
        return $firstUnionedType;
    }
    private function isTypeWithClassNameOnly(\PHPStan\Type\UnionType $unionType) : bool
    {
        foreach ($unionType->getTypes() as $unionedType) {
            if (!$unionedType instanceof \PHPStan\Type\TypeWithClassName) {
                return \false;
            }
        }
        return \true;
    }
}
