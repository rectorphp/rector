<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer;

use PhpParser\Node\FunctionLike;
use PHPStan\Type\MixedType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\TypeDeclaration\Contract\TypeInferer\ReturnTypeInfererInterface;
use Rector\TypeDeclaration\Sorter\TypeInfererSorter;
use Rector\TypeDeclaration\TypeAnalyzer\GenericClassStringTypeNormalizer;
use Rector\TypeDeclaration\TypeNormalizer;

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
        private PhpVersionProvider $phpVersionProvider
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
            return $this->genericClassStringTypeNormalizer->normalize($type);
        }

        return new MixedType();
    }

    public function verifyStaticType(Type $type, bool $isSupportedStaticReturnType): ?Type
    {
        if ($this->isStaticType($type)) {
            if (! $isSupportedStaticReturnType) {
                return null;
            }

            /** @var FullyQualifiedObjectType $type */
            return new ThisType($type->getClassName());
        }

        if (! $type instanceof UnionType) {
            return $type;
        }

        $returnTypes = $type->getTypes();
        $types = [];
        $hasStatic = false;
        foreach ($returnTypes as $returnType) {
            if ($this->isStaticType($returnType)) {
                /** @var FullyQualifiedObjectType $returnType */
                $types[] = new ThisType($returnType->getClassName());
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
    }

    private function isStaticType(Type $type): bool
    {
        return $type instanceof FullyQualifiedObjectType && $type->getClassName() === 'static';
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
}
