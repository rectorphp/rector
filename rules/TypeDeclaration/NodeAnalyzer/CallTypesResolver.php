<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\VariadicPlaceholder;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\StaticTypeMapper\Resolver\ClassNameFromObjectTypeResolver;
final class CallTypesResolver
{
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    /**
     * @readonly
     */
    private TypeFactory $typeFactory;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    public function __construct(NodeTypeResolver $nodeTypeResolver, TypeFactory $typeFactory, ReflectionProvider $reflectionProvider)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->typeFactory = $typeFactory;
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @param MethodCall[]|StaticCall[] $calls
     * @return array<int, Type>
     */
    public function resolveStrictTypesFromCalls(array $calls): array
    {
        $staticTypesByArgumentPosition = [];
        foreach ($calls as $call) {
            foreach ($call->args as $position => $arg) {
                if ($this->shouldSkipArg($arg)) {
                    return [];
                }
                /** @var Arg $arg */
                $staticTypesByArgumentPosition[$position][] = $this->resolveStrictArgValueType($arg);
            }
        }
        // unite to single type
        return $this->unionToSingleType($staticTypesByArgumentPosition);
    }
    /**
     * @param MethodCall[]|StaticCall[] $calls
     * @return array<int|string, Type>
     */
    public function resolveTypesFromCalls(array $calls): array
    {
        $staticTypesByArgumentPosition = [];
        foreach ($calls as $call) {
            foreach ($call->args as $position => $arg) {
                if ($this->shouldSkipArg($arg)) {
                    return [];
                }
                /** @var Arg $arg */
                if ($this->isEmptyArray($arg->value)) {
                    // skip empty array, as it doesn't add any value
                    continue;
                }
                $positionOrName = $arg->name instanceof Identifier ? $arg->name->toString() : $position;
                $staticTypesByArgumentPosition[$positionOrName][] = $this->resolveArgValueType($arg);
            }
        }
        // unite to single type
        return $this->unionToSingleType($staticTypesByArgumentPosition, \true);
    }
    private function resolveStrictArgValueType(Arg $arg): Type
    {
        $argValueType = $this->nodeTypeResolver->getNativeType($arg->value);
        return $this->normalizeType($argValueType);
    }
    private function resolveArgValueType(Arg $arg): Type
    {
        $argValueType = $this->nodeTypeResolver->getType($arg->value);
        return $this->normalizeType($argValueType);
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
    private function unionToSingleType(array $staticTypesByArgumentPosition, bool $removeMixedArray = \false): array
    {
        $staticTypeByArgumentPosition = [];
        foreach ($staticTypesByArgumentPosition as $position => $staticTypes) {
            if ($removeMixedArray) {
                $staticTypes = array_filter($staticTypes, fn(Type $type): bool => !$this->isArrayMixedMixedType($type));
            }
            $unionedType = $this->typeFactory->createMixedPassedOrUnionType($staticTypes);
            $staticTypeByArgumentPosition[$position] = $this->narrowParentObjectTreeToSingleObjectChildType($unionedType);
            if ($removeMixedArray && $staticTypeByArgumentPosition[$position] instanceof UnionType) {
                foreach ($staticTypeByArgumentPosition[$position]->getTypes() as $subType) {
                    if ($subType instanceof ArrayType && $this->isArrayMixedMixedType($subType)) {
                        $staticTypeByArgumentPosition[$position] = new MixedType();
                        continue 2;
                    }
                }
            }
        }
        return $staticTypeByArgumentPosition;
    }
    private function narrowParentObjectTreeToSingleObjectChildType(Type $type): Type
    {
        if (!$type instanceof UnionType) {
            return $type;
        }
        if (!$this->isTypeWithClassNameOnly($type)) {
            return $type;
        }
        $firstUnionedType = $type->getTypes()[0];
        foreach ($type->getTypes() as $unionedType) {
            $className = ClassNameFromObjectTypeResolver::resolve($unionedType);
            if ($className === null) {
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
            $className = ClassNameFromObjectTypeResolver::resolve($unionedType);
            if ($className === null) {
                return \false;
            }
        }
        return \true;
    }
    /**
     * @return \PHPStan\Type\MixedType|\PHPStan\Type\ObjectType|\PHPStan\Type\Type
     */
    private function normalizeType(Type $argValueType)
    {
        // "self" in another object is not correct, this make it independent
        $argValueType = $this->correctSelfType($argValueType);
        if (!$argValueType instanceof ObjectType) {
            return $argValueType;
        }
        // fix false positive generic type on string
        if (!$this->reflectionProvider->hasClass($argValueType->getClassName())) {
            return new MixedType();
        }
        return $argValueType;
    }
    /**
     * There is first class callable usage, or argument unpack, or named expr
     * simply returns array marks as unknown as can be anything and in any position
     * @param \PhpParser\Node\Arg|\PhpParser\Node\VariadicPlaceholder $arg
     */
    private function shouldSkipArg($arg): bool
    {
        if ($arg instanceof VariadicPlaceholder) {
            return \true;
        }
        return $arg->unpack;
    }
    private function isEmptyArray(Expr $expr): bool
    {
        if (!$expr instanceof Array_) {
            return \false;
        }
        return $expr->items === [];
    }
    private function isArrayMixedMixedType(Type $type): bool
    {
        if (!$type instanceof ArrayType && !$type instanceof ConstantArrayType) {
            return \false;
        }
        if (!$type->getItemType() instanceof MixedType && !$type->getItemType() instanceof NeverType) {
            return \false;
        }
        return $type->getKeyType() instanceof MixedType || $type->getKeyType() instanceof NeverType;
    }
}
