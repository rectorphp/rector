<?php

declare (strict_types=1);
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
    private $returnTypeInferers = [];
    /**
     * @var \Rector\TypeDeclaration\TypeNormalizer
     */
    private $typeNormalizer;
    /**
     * @var \Rector\TypeDeclaration\TypeAnalyzer\GenericClassStringTypeNormalizer
     */
    private $genericClassStringTypeNormalizer;
    /**
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    /**
     * @param ReturnTypeInfererInterface[] $returnTypeInferers
     */
    public function __construct(array $returnTypeInferers, \Rector\TypeDeclaration\TypeNormalizer $typeNormalizer, \Rector\TypeDeclaration\Sorter\TypeInfererSorter $typeInfererSorter, \Rector\TypeDeclaration\TypeAnalyzer\GenericClassStringTypeNormalizer $genericClassStringTypeNormalizer, \Rector\Core\Php\PhpVersionProvider $phpVersionProvider)
    {
        $this->typeNormalizer = $typeNormalizer;
        $this->genericClassStringTypeNormalizer = $genericClassStringTypeNormalizer;
        $this->phpVersionProvider = $phpVersionProvider;
        $this->returnTypeInferers = $typeInfererSorter->sort($returnTypeInferers);
    }
    public function inferFunctionLike(\PhpParser\Node\FunctionLike $functionLike) : \PHPStan\Type\Type
    {
        return $this->inferFunctionLikeWithExcludedInferers($functionLike, []);
    }
    /**
     * @param array<class-string<ReturnTypeInfererInterface>> $excludedInferers
     */
    public function inferFunctionLikeWithExcludedInferers(\PhpParser\Node\FunctionLike $functionLike, array $excludedInferers) : \PHPStan\Type\Type
    {
        $isSupportedStaticReturnType = $this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::STATIC_RETURN_TYPE);
        foreach ($this->returnTypeInferers as $returnTypeInferer) {
            if ($this->shouldSkipExcludedTypeInferer($returnTypeInferer, $excludedInferers)) {
                continue;
            }
            $originalType = $returnTypeInferer->inferFunctionLike($functionLike);
            if ($originalType instanceof \PHPStan\Type\MixedType) {
                continue;
            }
            $type = $this->typeNormalizer->normalizeArrayTypeAndArrayNever($originalType);
            // in case of void, check return type of children methods
            if ($type instanceof \PHPStan\Type\MixedType) {
                continue;
            }
            $type = $this->verifyStaticType($type, $isSupportedStaticReturnType);
            if (!$type instanceof \PHPStan\Type\Type) {
                continue;
            }
            // normalize ConstStringType to ClassStringType
            return $this->genericClassStringTypeNormalizer->normalize($type);
        }
        return new \PHPStan\Type\MixedType();
    }
    private function isStaticType(\PHPStan\Type\Type $type) : bool
    {
        return $type instanceof \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType && $type->getClassName() === 'static';
    }
    public function verifyStaticType(\PHPStan\Type\Type $type, bool $isSupportedStaticReturnType) : ?\PHPStan\Type\Type
    {
        if ($this->isStaticType($type)) {
            if (!$isSupportedStaticReturnType) {
                return null;
            }
            /** @var FullyQualifiedObjectType $type */
            return new \PHPStan\Type\ThisType($type->getClassName());
        }
        if (!$type instanceof \PHPStan\Type\UnionType) {
            return $type;
        }
        $returnTypes = $type->getTypes();
        $types = [];
        $hasStatic = \false;
        foreach ($returnTypes as $returnType) {
            if ($this->isStaticType($returnType)) {
                /** @var FullyQualifiedObjectType $returnType */
                $types[] = new \PHPStan\Type\ThisType($returnType->getClassName());
                $hasStatic = \true;
                continue;
            }
            $types[] = $returnType;
        }
        if (!$hasStatic) {
            return $type;
        }
        if (!$isSupportedStaticReturnType) {
            return null;
        }
        return new \PHPStan\Type\UnionType($types);
    }
    /**
     * @param array<class-string<ReturnTypeInfererInterface>> $excludedInferers
     */
    private function shouldSkipExcludedTypeInferer(\Rector\TypeDeclaration\Contract\TypeInferer\ReturnTypeInfererInterface $returnTypeInferer, array $excludedInferers) : bool
    {
        foreach ($excludedInferers as $excludedInferer) {
            if (\is_a($returnTypeInferer, $excludedInferer)) {
                return \true;
            }
        }
        return \false;
    }
}
