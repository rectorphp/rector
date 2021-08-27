<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeInferer;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\UnionType as PhpParserUnionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Core\Configuration\Option;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\TypeDeclaration\Contract\TypeInferer\ReturnTypeInfererInterface;
use Rector\TypeDeclaration\Sorter\TypeInfererSorter;
use Rector\TypeDeclaration\TypeAnalyzer\GenericClassStringTypeNormalizer;
use Rector\TypeDeclaration\TypeNormalizer;
use RectorPrefix20210827\Symplify\PackageBuilder\Parameter\ParameterProvider;
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
     * @var \Symplify\PackageBuilder\Parameter\ParameterProvider
     */
    private $parameterProvider;
    /**
     * @param ReturnTypeInfererInterface[] $returnTypeInferers
     */
    public function __construct(array $returnTypeInferers, \Rector\TypeDeclaration\TypeNormalizer $typeNormalizer, \Rector\TypeDeclaration\Sorter\TypeInfererSorter $typeInfererSorter, \Rector\TypeDeclaration\TypeAnalyzer\GenericClassStringTypeNormalizer $genericClassStringTypeNormalizer, \Rector\Core\Php\PhpVersionProvider $phpVersionProvider, \RectorPrefix20210827\Symplify\PackageBuilder\Parameter\ParameterProvider $parameterProvider)
    {
        $this->typeNormalizer = $typeNormalizer;
        $this->genericClassStringTypeNormalizer = $genericClassStringTypeNormalizer;
        $this->phpVersionProvider = $phpVersionProvider;
        $this->parameterProvider = $parameterProvider;
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
        $isAutoImport = $this->parameterProvider->provideBoolParameter(\Rector\Core\Configuration\Option::AUTO_IMPORT_NAMES);
        $isAutoImportFullyQuafiedReturn = $this->isAutoImportWithFullyQualifiedReturn($isAutoImport, $functionLike);
        if ($isAutoImportFullyQuafiedReturn) {
            return new \PHPStan\Type\MixedType();
        }
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
    private function isAutoImportWithFullyQualifiedReturn(bool $isAutoImport, \PhpParser\Node\FunctionLike $functionLike) : bool
    {
        if (!$isAutoImport) {
            return \false;
        }
        if (!$functionLike instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return \false;
        }
        if ($this->isNamespacedFullyQualified($functionLike->returnType)) {
            return \true;
        }
        if (!$functionLike->returnType instanceof \PhpParser\Node\UnionType) {
            return \false;
        }
        $types = $functionLike->returnType->types;
        foreach ($types as $type) {
            if ($this->isNamespacedFullyQualified($type)) {
                return \true;
            }
        }
        return \false;
    }
    private function isNamespacedFullyQualified(?\PhpParser\Node $node) : bool
    {
        return $node instanceof \PhpParser\Node\Name\FullyQualified && \strpos($node->toString(), '\\') !== \false;
    }
    private function isStaticType(\PHPStan\Type\Type $type) : bool
    {
        return $type instanceof \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType && $type->getClassName() === 'static';
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
