<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\UnionType as PhpParserUnionType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DeadCode\PhpDoc\TagRemover\ParamTagRemover;
use Rector\DeadCode\PhpDoc\TagRemover\ReturnTagRemover;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeAnalyzer;
use Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodParamVendorLockResolver;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\UnionTypesRectorTest
 */
final class UnionTypesRector extends AbstractRector implements MinPhpVersionInterface
{
    public function __construct(
        private ReturnTagRemover $returnTagRemover,
        private ParamTagRemover $paramTagRemover,
        private ClassMethodParamVendorLockResolver $classMethodParamVendorLockResolver,
        private UnionTypeAnalyzer $unionTypeAnalyzer,
        private TypeFactory $typeFactory
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change docs types to union types, where possible (properties are covered by TypedPropertiesRector)',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @param array|int $number
     * @return bool|float
     */
    public function go($number)
    {
    }
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function go(array|int $number): bool|float
    {
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Function_::class, Closure::class, ArrowFunction::class];
    }

    /**
     * @param ClassMethod | Function_ | Closure | ArrowFunction $node
     */
    public function refactor(Node $node): ?Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);

        $this->refactorParamTypes($node, $phpDocInfo);
        $this->refactorReturnType($node, $phpDocInfo);

        $this->paramTagRemover->removeParamTagsIfUseless($phpDocInfo, $node);
        $this->returnTagRemover->removeReturnTagIfUseless($phpDocInfo, $node);

        return $node;
    }

    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::UNION_TYPES;
    }

    private function refactorParamTypes(
        ClassMethod | Function_ | Closure | ArrowFunction $functionLike,
        PhpDocInfo $phpDocInfo
    ): void {
        if ($functionLike instanceof ClassMethod && $this->classMethodParamVendorLockResolver->isVendorLocked(
            $functionLike
        )) {
            return;
        }

        foreach ($functionLike->getParams() as $param) {
            /** @var string $paramName */
            $paramName = $this->getName($param->var);
            $paramType = $phpDocInfo->getParamType($paramName);
            if (! $paramType instanceof UnionType) {
                continue;
            }

            if ($this->unionTypeAnalyzer->hasObjectWithoutClassType($paramType)) {
                $this->changeObjectWithoutClassType($param, $paramType);
                continue;
            }

            $uniqueatedParamType = $this->filterOutDuplicatedArrayTypes($paramType);
            if (! $uniqueatedParamType instanceof UnionType) {
                continue;
            }

            $phpParserUnionType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode(
                $uniqueatedParamType,
                TypeKind::PARAM()
            );

            if (! $phpParserUnionType instanceof PhpParserUnionType) {
                continue;
            }

            if ($param->type instanceof PhpParserUnionType) {
                continue;
            }

            $param->type = $phpParserUnionType;
        }
    }

    private function changeObjectWithoutClassType(Param $param, UnionType $unionType): void
    {
        if (! $this->unionTypeAnalyzer->hasObjectWithoutClassTypeWithOnlyFullyQualifiedObjectType($unionType)) {
            return;
        }

        $param->type = new Name('object');
    }

    private function refactorReturnType(
        ClassMethod | Function_ | Closure | ArrowFunction $functionLike,
        PhpDocInfo $phpDocInfo
    ): void {
        // do not override existing return type
        if ($functionLike->getReturnType() !== null) {
            return;
        }

        $returnType = $phpDocInfo->getReturnType();
        if (! $returnType instanceof UnionType) {
            return;
        }

        $uniqueatedReturnType = $this->filterOutDuplicatedArrayTypes($returnType);
        if (! $uniqueatedReturnType instanceof UnionType) {
            return;
        }

        $phpParserUnionType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode(
            $uniqueatedReturnType,
            TypeKind::RETURN()
        );

        if (! $phpParserUnionType instanceof PhpParserUnionType) {
            return;
        }

        $functionLike->returnType = $phpParserUnionType;
    }

    private function filterOutDuplicatedArrayTypes(UnionType $unionType): UnionType | Type
    {
        $hasArrayType = false;
        $singleArrayTypes = [];

        $originalTypeCount = count($unionType->getTypes());

        foreach ($unionType->getTypes() as $unionedType) {
            if ($unionedType instanceof ArrayType) {
                if ($hasArrayType) {
                    continue;
                }

                $singleArrayTypes[] = $unionedType;
                $hasArrayType = true;
                continue;
            }

            $singleArrayTypes[] = $unionedType;
        }

        if ($originalTypeCount === count($singleArrayTypes)) {
            return $unionType;
        }

        return $this->typeFactory->createMixedPassedOrUnionType($singleArrayTypes);
    }
}
