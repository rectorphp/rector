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
use PHPStan\Type\MixedType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use PHPStan\Type\VoidType;
use Rector\Core\Configuration\Option;
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
        private BetterNodeFinder $betterNodeFinder
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
        $isAutoImportFullyQuafiedReturn = $this->isAutoImportWithFullyQualifiedReturn($isAutoImport, $functionLike);
        if ($isAutoImportFullyQuafiedReturn) {
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
