<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\UnionType as PhpParserUnionType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\UnionType;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PropertyDocBlockManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\PhpDoc\TagRemover\ParamTagRemover;
use Rector\DeadCode\PhpDoc\TagRemover\ReturnTagRemover;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodParamVendorLockResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;

/**
 * @see \Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\UnionTypesRectorTest
 */
final class UnionTypesRector extends AbstractRector
{
    /**
     * @var ReturnTagRemover
     */
    private $returnTagRemover;

    /**
     * @var ParamTagRemover
     */
    private $paramTagRemover;

    /**
     * @var PropertyDocBlockManipulator
     */
    private $propertyDocBlockManipulator;

    /**
     * @var PhpDocTypeChanger
     */
    private $phpDocTypeChanger;

    public function __construct(
        ReturnTagRemover $returnTagRemover,
        ParamTagRemover $paramTagRemover,
        ClassMethodParamVendorLockResolver $classMethodParamVendorLockResolver,
        PhpDocTypeChanger $phpDocTypeChanger
    ) {
        $this->returnTagRemover = $returnTagRemover;
        $this->paramTagRemover = $paramTagRemover;
        $this->classMethodParamVendorLockResolver = $classMethodParamVendorLockResolver;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
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
     * @param ClassMethod|Function_|Closure|ArrowFunction $node
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

    /**
     * @param ClassMethod|Function_|Closure|ArrowFunction $functionLike
     */
    private function refactorParamTypes(FunctionLike $functionLike, PhpDocInfo $phpDocInfo): void
    {
        if ($functionLike instanceof ClassMethod && $this->classMethodParamVendorLockResolver->isVendorLocked(
            $functionLike
        )) {
            return;
        }

        foreach ($functionLike->getParams() as $param) {
            if ($param->type !== null) {
                continue;
            }

            /** @var string $paramName */
            $paramName = $this->getName($param->var);
            $paramType = $phpDocInfo->getParamType($paramName);
            if (! $paramType instanceof UnionType) {
                continue;
            }

            if ($this->hasObjectWithoutClassType($paramType)) {
                if (! $this->hasObjectWithoutClassTypeWithOnlyFullyQualifiedObjectType($paramType)) {
                    continue;
                }

                $param->type = new Name('object');
                $this->cleanParamObjectType($paramType, $phpDocInfo, $param, $paramName);
                continue;
            }

            $phpParserUnionType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($paramType);
            if (! $phpParserUnionType instanceof PhpParserUnionType) {
                continue;
            }

            $param->type = $phpParserUnionType;
        }
    }

    private function hasObjectWithoutClassType(UnionType $unionType): bool
    {
        $types = $unionType->getTypes();
        foreach ($types as $type) {
            if ($type instanceof ObjectWithoutClassType) {
                return true;
            }
        }

        return false;
    }

    private function hasObjectWithoutClassTypeWithOnlyFullyQualifiedObjectType(UnionType $unionType): bool
    {
        $types = $unionType->getTypes();
        foreach ($types as $type) {
            if ($type instanceof ObjectWithoutClassType) {
                continue;
            }

            if (! $type instanceof FullyQualifiedObjectType) {
                return false;
            }
        }

        return true;
    }

    private function cleanParamObjectType(UnionType $unionType, PhpDocInfo $phpDocInfo, Param $param, string $paramName): void
    {
        $types = $unionType->getTypes();
        foreach ($types as $key => $type) {
            if ($type instanceof ObjectWithoutClassType) {
                unset($types[$key]);
            }
        }

        $currentType = current($types);
        $type = count($types) === 1
            ? new ObjectType($currentType->getClassName())
            : new UnionType($types);

        $this->phpDocTypeChanger->changeParamType($phpDocInfo, $type, $param, $paramName);
    }

    /**
     * @param ClassMethod|Function_|Closure|ArrowFunction $functionLike
     */
    private function refactorReturnType(FunctionLike $functionLike, PhpDocInfo $phpDocInfo): void
    {
        // do not override existing return type
        if ($functionLike->getReturnType() !== null) {
            return;
        }

        $returnType = $phpDocInfo->getReturnType();
        if (! $returnType instanceof UnionType) {
            return;
        }

        $phpParserUnionType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($returnType);
        if (! $phpParserUnionType instanceof PhpParserUnionType) {
            return;
        }

        $functionLike->returnType = $phpParserUnionType;
    }
}
