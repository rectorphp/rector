<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\UnionType as PhpParserUnionType;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadDocBlock\TagRemover\ParamTagRemover;
use Rector\DeadDocBlock\TagRemover\ReturnTagRemover;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Php80\Tests\Rector\FunctionLike\UnionTypesRector\UnionTypesRectorTest
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

    public function __construct(ReturnTagRemover $returnTagRemover, ParamTagRemover $paramTagRemover)
    {
        $this->returnTagRemover = $returnTagRemover;
        $this->paramTagRemover = $paramTagRemover;
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
     * @return string[]
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

            $phpParserUnionType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($paramType);
            if (! $phpParserUnionType instanceof PhpParserUnionType) {
                continue;
            }

            $param->type = $phpParserUnionType;
        }
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
