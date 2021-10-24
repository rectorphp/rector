<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\UnionType as PhpParserUnionType;
use PHPStan\Reflection\ClassReflection;
<<<<<<< HEAD
use PHPStan\Reflection\ReflectionProvider;
=======
>>>>>>> ThisType now accepts object type
use PHPStan\Type\MixedType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use PHPStan\Type\VoidType;
use Rector\Core\Configuration\Option;
<<<<<<< HEAD
use Rector\Core\Enum\ObjectReference;
<<<<<<< HEAD
use Rector\Core\Exception\ShouldNotHappenException;
=======
=======
use Rector\Core\Exception\ShouldNotHappenException;
>>>>>>> ThisType now accepts object type
>>>>>>> ThisType now accepts object type
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\TypeDeclaration\Contract\TypeInferer\ReturnTypeInfererInterface;
use Rector\TypeDeclaration\Sorter\TypeInfererSorter;
use Rector\TypeDeclaration\TypeAnalyzer\GenericClassStringTypeNormalizer;
use Rector\TypeDeclaration\TypeNormalizer;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class ReturnTypeInferer
{
    /**
     * @var ReturnTypeInfererInterface[]
     */
    private array $returnTypeInferers = [];

    /**
     * @param ReturnTypeInfererInterface[] $returnTypeInferers
     */
    public function __construct(
        array $returnTypeInferers,
        private TypeNormalizer $typeNormalizer,
        TypeInfererSorter $typeInfererSorter,
        private GenericClassStringTypeNormalizer $genericClassStringTypeNormalizer,
        private PhpVersionProvider $phpVersionProvider,
        private ParameterProvider $parameterProvider,
        private BetterNodeFinder $betterNodeFinder,
        private ReflectionProvider $reflectionProvider,
    ) {
        $this->returnTypeInferers = $typeInfererSorter->sort($returnTypeInferers);
    }

    public function inferFunctionLike(FunctionLike $functionLike): Type
    {
        return $this->inferFunctionLikeWithExcludedInferers($functionLike, []);
    }

    /**
     * @param array<class-string<ReturnTypeInfererInterface>> $excludedInferers
     */
    public function inferFunctionLikeWithExcludedInferers(FunctionLike $functionLike, array $excludedInferers): Type
    {
        $isSupportedStaticReturnType = $this->phpVersionProvider->isAtLeastPhpVersion(
            PhpVersionFeature::STATIC_RETURN_TYPE
        );

        $isAutoImport = $this->parameterProvider->provideBoolParameter(Option::AUTO_IMPORT_NAMES);
        if ($this->isAutoImportWithFullyQualifiedReturn($isAutoImport, $functionLike)) {
            return new MixedType();
        }

        foreach ($this->returnTypeInferers as $returnTypeInferer) {
            if ($this->shouldSkipExcludedTypeInferer($returnTypeInferer, $excludedInferers)) {
                continue;
            }

            $originalType = $returnTypeInferer->inferFunctionLike($functionLike);
            if ($originalType instanceof MixedType) {
                continue;
            }

            $type = $this->typeNormalizer->normalizeArrayTypeAndArrayNever($originalType);

            // in case of void, check return type of children methods
            if ($type instanceof MixedType) {
                continue;
            }

            $type = $this->verifyStaticType($type, $isSupportedStaticReturnType);
            if (! $type instanceof Type) {
                continue;
            }

            // normalize ConstStringType to ClassStringType
            $resolvedType = $this->genericClassStringTypeNormalizer->normalize($type);

            return $this->resolveTypeWithVoidHandling($functionLike, $resolvedType);
        }

        return new MixedType();
    }

    public function verifyStaticType(Type $type, bool $isSupportedStaticReturnType): ?Type
    {
        if ($this->isStaticType($type)) {
<<<<<<< HEAD
            /** @var TypeWithClassName $type */
=======
            /** @var FullyQualifiedObjectType $type */
>>>>>>> ThisType
            return $this->resolveStaticType($isSupportedStaticReturnType, $type);
        }

        if ($type instanceof UnionType) {
            return $this->resolveUnionStaticTypes($type, $isSupportedStaticReturnType);
        }

<<<<<<< HEAD
<<<<<<< HEAD
        return $type;
=======
        $returnTypes = $type->getTypes();
        $types = [];
        $hasStatic = false;
        foreach ($returnTypes as $returnType) {
            if ($this->isStaticType($returnType)) {
                /** @var \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType $returnType */
                $returnTypeClassReflection = $returnType->getClassReflection();
                if (! $returnTypeClassReflection instanceof ClassReflection) {
                    throw new ShouldNotHappenException();
                }

                $types[] = new ThisType($returnTypeClassReflection);
                $hasStatic = true;
                continue;
            }

            $types[] = $returnType;
        }

        if (! $hasStatic) {
            return $type;
        }

        if (! $isSupportedStaticReturnType) {
            return null;
        }

        return new UnionType($types);
>>>>>>> ThisType now accepts object type
=======
        return $this->resolveUnionStaticTypes($type, $isSupportedStaticReturnType);
>>>>>>> ThisType
    }

    private function resolveTypeWithVoidHandling(FunctionLike $functionLike, Type $resolvedType): Type
    {
        if ($resolvedType instanceof VoidType) {
            $hasReturnValue = (bool) $this->betterNodeFinder->findFirst(
                (array) $functionLike->getStmts(),
                function (Node $subNode): bool {
                    if (! $subNode instanceof Return_) {
                        return false;
                    }

                    return $subNode->expr instanceof Expr;
                }
            );

            if ($hasReturnValue) {
                return new MixedType();
            }
        }

        return $resolvedType;
    }

    private function isAutoImportWithFullyQualifiedReturn(bool $isAutoImport, FunctionLike $functionLike): bool
    {
        if (! $isAutoImport) {
            return false;
        }

        if (! $functionLike instanceof ClassMethod) {
            return false;
        }

        if ($this->isNamespacedFullyQualified($functionLike->returnType)) {
            return true;
        }

        if (! $functionLike->returnType instanceof PhpParserUnionType) {
            return false;
        }

        $types = $functionLike->returnType->types;
        foreach ($types as $type) {
            if ($this->isNamespacedFullyQualified($type)) {
                return true;
            }
        }

        return false;
    }

    private function isNamespacedFullyQualified(?Node $node): bool
    {
        return $node instanceof FullyQualified && str_contains($node->toString(), '\\');
    }

    private function isStaticType(Type $type): bool
    {
        if (! $type instanceof TypeWithClassName) {
            return false;
        }

        return $type->getClassName() === ObjectReference::STATIC()->getValue();
    }

    /**
     * @param array<class-string<ReturnTypeInfererInterface>> $excludedInferers
     */
    private function shouldSkipExcludedTypeInferer(
        ReturnTypeInfererInterface $returnTypeInferer,
        array $excludedInferers
    ): bool {
        foreach ($excludedInferers as $excludedInferer) {
            if (is_a($returnTypeInferer, $excludedInferer)) {
                return true;
            }
        }

        return false;
    }

<<<<<<< HEAD
    private function resolveUnionStaticTypes(UnionType $unionType, bool $isSupportedStaticReturnType): UnionType|null
    {
        $resolvedTypes = [];
        $hasStatic = false;

        foreach ($unionType->getTypes() as $unionedType) {
            if ($this->isStaticType($unionedType)) {
                /** @var FullyQualifiedObjectType $unionedType */
                $classReflection = $this->reflectionProvider->getClass($unionedType->getClassName());

                $resolvedTypes[] = new ThisType($classReflection);
                $hasStatic = true;
                continue;
            }

<<<<<<< HEAD
            $resolvedTypes[] = $unionedType;
=======
            $types[] = $returnType;
=======
    private function resolveStaticType(bool $isSupportedStaticReturnType, FullyQualifiedObjectType $type): ?ThisType
    {
        if (! $isSupportedStaticReturnType) {
            return null;
        }

        $classReflection = $type->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            throw new ShouldNotHappenException();
        }

        return new ThisType($classReflection);
    }

    private function resolveUnionStaticTypes(
        UnionType $unionType,
        bool $isSupportedStaticReturnType
    ): UnionType|null|Type
    {
        $types = [];
        $hasStatic = false;

        foreach ($unionType->getTypes() as $returnType) {
            if (! $this->isStaticType($returnType)) {
                $types[] = $returnType;
                continue;
            }

            /** @var \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType $returnType */
            $returnTypeClassReflection = $returnType->getClassReflection();
            if (! $returnTypeClassReflection instanceof ClassReflection) {
                throw new ShouldNotHappenException();
            }

            $types[] = new ThisType($returnTypeClassReflection);
            $hasStatic = true;
>>>>>>> ThisType
>>>>>>> ThisType now accepts object type
        }

        if (! $hasStatic) {
            return $unionType;
        }

<<<<<<< HEAD
        // has static, but it is not supported
=======
<<<<<<< HEAD
=======
        // has static, but it is not supported
>>>>>>> ThisType
>>>>>>> ThisType now accepts object type
        if (! $isSupportedStaticReturnType) {
            return null;
        }

        return new UnionType($resolvedTypes);
    }
<<<<<<< HEAD

    private function resolveStaticType(
        bool $isSupportedStaticReturnType,
        TypeWithClassName $typeWithClassName
    ): ?ThisType {
        if (! $isSupportedStaticReturnType) {
            return null;
        }

        $classReflection = $typeWithClassName->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            throw new ShouldNotHappenException();
        }

        return new ThisType($classReflection);
    }
=======
>>>>>>> ThisType
}
