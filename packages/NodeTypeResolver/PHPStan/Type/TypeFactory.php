<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PHPStan\Type;

<<<<<<< HEAD
=======
<<<<<<< HEAD
use PHPStan\Reflection\ReflectionProvider;
=======
use PHPStan\Reflection\ClassReflection;
>>>>>>> StaticType requires ClassReflection on constructor
>>>>>>> NativeFunctionReflection has new parameter
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantFloatType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\FloatType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\VerbosityLevel;
<<<<<<< HEAD
=======
<<<<<<< HEAD
use Rector\Core\Enum\ObjectReference;
<<<<<<< HEAD
use Rector\Core\Exception\ShouldNotHappenException;
=======
=======
use Rector\Core\Exception\ShouldNotHappenException;
>>>>>>> correct StaticType
>>>>>>> StaticType requires ClassReflection on constructor
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\ValueObject\PhpVersionFeature;
>>>>>>> NativeFunctionReflection has new parameter
use Rector\StaticTypeMapper\TypeFactory\UnionTypeFactory;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;

final class TypeFactory
{
    public function __construct(
        private UnionTypeFactory $unionTypeFactory,
    ) {
    }

    /**
     * @param Type[] $types
     */
    public function createMixedPassedOrUnionTypeAndKeepConstant(array $types): Type
    {
        $types = $this->unwrapUnionedTypes($types);
        $types = $this->uniquateTypes($types, true);

        return $this->createUnionOrSingleType($types);
    }

    /**
     * @param Type[] $types
     */
    public function createMixedPassedOrUnionType(array $types): Type
    {
        $types = $this->unwrapUnionedTypes($types);
        $types = $this->uniquateTypes($types);

        return $this->createUnionOrSingleType($types);
    }

    /**
     * @template TType as Type
     * @param array<TType> $types
     * @return array<TType>
     */
    public function uniquateTypes(array $types, bool $keepConstant = false): array
    {
        $uniqueTypes = [];
        foreach ($types as $type) {
            if (! $keepConstant) {
                $type = $this->removeValueFromConstantType($type);
            }

            $type = $this->normalizeObjectTypes($type);

            $typeHash = $type->describe(VerbosityLevel::cache());
            $uniqueTypes[$typeHash] = $type;
        }

        // re-index
        return array_values($uniqueTypes);
    }

    /**
     * @param Type[] $types
     * @return Type[]
     */
    private function unwrapUnionedTypes(array $types): array
    {
        // unwrap union types
        $unwrappedTypes = [];
        foreach ($types as $type) {
            $flattenTypes = TypeUtils::flattenTypes($type);

            foreach ($flattenTypes as $flattenType) {
                if ($flattenType instanceof ConstantArrayType) {
                    $unwrappedTypes = array_merge($unwrappedTypes, $this->unwrapConstantArrayTypes($flattenType));
                } else {
                    $unwrappedTypes = $this->resolveNonConstantArrayType($flattenType, $unwrappedTypes);
                }
            }
        }

        return $unwrappedTypes;
    }

    /**
     * @param Type[] $unwrappedTypes
     * @return Type[]
     */
    private function resolveNonConstantArrayType(Type $type, array $unwrappedTypes): array
    {
        if ($type instanceof FullyQualifiedObjectType && $type->getClassName() === 'Rector\Core\Stubs\DummyTraitClass') {
            return $unwrappedTypes;
        }

        $unwrappedTypes[] = $type;
        return $unwrappedTypes;
    }

    /**
     * @param Type[] $types
     */
    private function createUnionOrSingleType(array $types): Type
    {
        if ($types === []) {
            return new MixedType();
        }

        if (count($types) === 1) {
            return $types[0];
        }

        return $this->unionTypeFactory->createUnionObjectType($types);
    }

    private function removeValueFromConstantType(Type $type): Type
    {
        // remove values from constant types
        if ($type instanceof ConstantFloatType) {
            return new FloatType();
        }

        if ($type instanceof ConstantStringType) {
            return new StringType();
        }

        if ($type instanceof ConstantIntegerType) {
            return new IntegerType();
        }

        if ($type instanceof ConstantBooleanType) {
            return new BooleanType();
        }

        return $type;
    }

    /**
     * @return Type[]
     */
    private function unwrapConstantArrayTypes(ConstantArrayType $constantArrayType): array
    {
        $unwrappedTypes = [];

        $flattenKeyTypes = TypeUtils::flattenTypes($constantArrayType->getKeyType());
        $flattenItemTypes = TypeUtils::flattenTypes($constantArrayType->getItemType());

        foreach ($flattenItemTypes as $position => $nestedFlattenItemType) {
            /** @var Type|null $nestedFlattenKeyType */
            $nestedFlattenKeyType = $flattenKeyTypes[$position] ?? null;
            if ($nestedFlattenKeyType === null) {
                $nestedFlattenKeyType = new MixedType();
            }

            $unwrappedTypes[] = new ArrayType($nestedFlattenKeyType, $nestedFlattenItemType);
        }

        return $unwrappedTypes;
    }

    private function normalizeObjectTypes(Type $type): Type
    {
<<<<<<< HEAD
        return TypeTraverser::map($type, function (Type $currentType, callable $traverseCallback): Type {
            if ($currentType instanceof ShortenedObjectType) {
                return new FullyQualifiedObjectType($currentType->getFullyQualifiedName());
=======
        return TypeTraverser::map($type, function (Type $traversedType, callable $traverseCallback): Type {
            if ($this->isStatic($traversedType) && $this->phpVersionProvider->isAtLeastPhpVersion(
                PhpVersionFeature::STATIC_RETURN_TYPE
            )) {
                /** @var ObjectType $traversedType */
<<<<<<< HEAD
<<<<<<< HEAD
                $className = $traversedType->getClassName();
                if (! $this->reflectionProvider->hasClass($className)) {
                    throw new ShouldNotHappenException();
                }

                $classReflection = $this->reflectionProvider->getClass($className);
                return new ThisType($classReflection);
=======
=======
>>>>>>> StaticType requires ClassReflection on constructor
<<<<<<< HEAD
                return new ThisType($traversedType->getClassName());
=======
                return $this->normalizeStaticType($traversedType);
>>>>>>> bd713da77... getFileName() now returns null
<<<<<<< HEAD
>>>>>>> PHPStan\Reflection\ClassReflection::getFileName() now returns null|string
<<<<<<< HEAD
>>>>>>> PHPStan\Reflection\ClassReflection::getFileName() now returns null|string
=======
=======
=======
                $classReflection = $traversedType->getClassReflection();
                if (! $classReflection instanceof ClassReflection) {
                    throw new ShouldNotHappenException();
                }

                return new ThisType($classReflection);
>>>>>>> 4971e22ca... correct StaticType
>>>>>>> StaticType requires ClassReflection on constructor
>>>>>>> NativeFunctionReflection has new parameter
            }

            if ($currentType instanceof ObjectType && ! $currentType instanceof GenericObjectType && ! $currentType instanceof AliasedObjectType && $currentType->getClassName() !== 'Iterator') {
                return new FullyQualifiedObjectType($currentType->getClassName());
            }

            return $traverseCallback($currentType);
        });
    }
<<<<<<< HEAD
=======

    private function isStatic(Type $type): bool
    {
        if (! $type instanceof ObjectType) {
            return false;
        }

        return $type->getClassName() === ObjectReference::STATIC()->getValue();
    }

    private function isSelf(Type $type): bool
    {
        if (! $type instanceof ObjectType) {
            return false;
        }

        return $type->getClassName() === ObjectReference::SELF()->getValue();
    }

    private function normalizeStaticType(ObjectType $objectType): ThisType
    {
        $classReflection = $objectType->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            throw new ShouldNotHappenException();
        }

        return new ThisType($classReflection);
    }
>>>>>>> PHPStan\Reflection\ClassReflection::getFileName() now returns null|string
}
