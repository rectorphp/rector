<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeInferer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\UnionType as PhpParserUnionType;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MixedType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use PHPStan\Type\VoidType;
use Rector\Core\Configuration\Option;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\TypeDeclaration\Contract\TypeInferer\ReturnTypeInfererInterface;
use Rector\TypeDeclaration\Sorter\TypeInfererSorter;
use Rector\TypeDeclaration\TypeAnalyzer\GenericClassStringTypeNormalizer;
use Rector\TypeDeclaration\TypeNormalizer;
use RectorPrefix20211123\Symplify\PackageBuilder\Parameter\ParameterProvider;
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
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @param ReturnTypeInfererInterface[] $returnTypeInferers
     */
    public function __construct(array $returnTypeInferers, \Rector\TypeDeclaration\TypeNormalizer $typeNormalizer, \Rector\TypeDeclaration\Sorter\TypeInfererSorter $typeInfererSorter, \Rector\TypeDeclaration\TypeAnalyzer\GenericClassStringTypeNormalizer $genericClassStringTypeNormalizer, \Rector\Core\Php\PhpVersionProvider $phpVersionProvider, \RectorPrefix20211123\Symplify\PackageBuilder\Parameter\ParameterProvider $parameterProvider, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->typeNormalizer = $typeNormalizer;
        $this->genericClassStringTypeNormalizer = $genericClassStringTypeNormalizer;
        $this->phpVersionProvider = $phpVersionProvider;
        $this->parameterProvider = $parameterProvider;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->reflectionProvider = $reflectionProvider;
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
        if ($this->isAutoImportWithFullyQualifiedReturn($isAutoImport, $functionLike)) {
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
            $resolvedType = $this->genericClassStringTypeNormalizer->normalize($type);
            return $this->resolveTypeWithVoidHandling($functionLike, $resolvedType);
        }
        return new \PHPStan\Type\MixedType();
    }
    public function verifyStaticType(\PHPStan\Type\Type $type, bool $isSupportedStaticReturnType) : ?\PHPStan\Type\Type
    {
        if ($this->isStaticType($type)) {
            /** @var TypeWithClassName $type */
            return $this->resolveStaticType($isSupportedStaticReturnType, $type);
        }
        if ($type instanceof \PHPStan\Type\UnionType) {
            return $this->resolveUnionStaticTypes($type, $isSupportedStaticReturnType);
        }
        return $type;
    }
    private function resolveTypeWithVoidHandling(\PhpParser\Node\FunctionLike $functionLike, \PHPStan\Type\Type $resolvedType) : \PHPStan\Type\Type
    {
        if ($resolvedType instanceof \PHPStan\Type\VoidType) {
            $hasReturnValue = (bool) $this->betterNodeFinder->findFirst((array) $functionLike->getStmts(), function (\PhpParser\Node $subNode) : bool {
                if (!$subNode instanceof \PhpParser\Node\Stmt\Return_) {
                    return \false;
                }
                return $subNode->expr instanceof \PhpParser\Node\Expr;
            });
            if ($hasReturnValue) {
                return new \PHPStan\Type\MixedType();
            }
        }
        return $resolvedType;
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
        if (!$type instanceof \PHPStan\Type\TypeWithClassName) {
            return \false;
        }
        return $type->getClassName() === \Rector\Core\Enum\ObjectReference::STATIC()->getValue();
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
    /**
     * @return \PHPStan\Type\UnionType|null
     */
    private function resolveUnionStaticTypes(\PHPStan\Type\UnionType $unionType, bool $isSupportedStaticReturnType)
    {
        $resolvedTypes = [];
        $hasStatic = \false;
        foreach ($unionType->getTypes() as $unionedType) {
            if ($this->isStaticType($unionedType)) {
                /** @var FullyQualifiedObjectType $unionedType */
                $classReflection = $this->reflectionProvider->getClass($unionedType->getClassName());
                $resolvedTypes[] = new \PHPStan\Type\ThisType($classReflection);
                $hasStatic = \true;
                continue;
            }
            $resolvedTypes[] = $unionedType;
        }
        if (!$hasStatic) {
            return $unionType;
        }
        // has static, but it is not supported
        if (!$isSupportedStaticReturnType) {
            return null;
        }
        return new \PHPStan\Type\UnionType($resolvedTypes);
    }
    private function resolveStaticType(bool $isSupportedStaticReturnType, \PHPStan\Type\TypeWithClassName $typeWithClassName) : ?\PHPStan\Type\ThisType
    {
        if (!$isSupportedStaticReturnType) {
            return null;
        }
        $classReflection = $typeWithClassName->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        return new \PHPStan\Type\ThisType($classReflection);
    }
}
