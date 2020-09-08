<?php

declare(strict_types=1);

namespace Rector\Downgrade\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Downgrade\Tests\Rector\Property\DowngradeUnionTypeToDocBlockRector\DowngradeUnionTypeToDocBlockRectorTest
 */
final class DowngradeUnionTypeToDocBlockRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Downgrade union types to doc block', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public int|string $value;

    public function run(): int|string
    {
        $this->value;
    }
}
PHP

                ,
                <<<'PHP'
class SomeClass
{
    /**
     * @var int|string
     */
    public $value;

    /**
     * @return int|string
     */
    public function run()
    {
        $this->value;
    }
}
PHP

            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Property::class, ClassMethod::class];
    }

    /**
     * @param Property|ClassMethod|Function_|Closure|ArrowFunction $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Property) {
            return $this->refactorProperty($node);
        }

        if ($node instanceof FunctionLike) {
            return $this->refactorClassMethod($node);
        }

        return null;
    }

    private function refactorProperty(Property $property): ?Property
    {
        if (! $property->type instanceof UnionType) {
            return null;
        }

        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            $phpDocInfo = $this->phpDocInfoFactory->createEmpty($property);
        }

        $phpStanType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($property->type);
        $phpDocInfo->changeVarType($phpStanType);

        $property->type = null;

        return $property;
    }

    /**
     * @param ClassMethod|Function_|Closure|ArrowFunction $functionLike
     */
    private function refactorClassMethod(FunctionLike $functionLike): FunctionLike
    {
        $this->processReturnType($functionLike);
        $this->processParamTypes($functionLike);

        return $functionLike;
    }

    /**
     * @param ClassMethod|Function_|Closure|ArrowFunction $functionLike
     */
    private function processReturnType(FunctionLike $functionLike): void
    {
        if (! $functionLike->returnType instanceof UnionType) {
            return;
        }

        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $functionLike->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            $phpDocInfo = $this->phpDocInfoFactory->createEmpty($functionLike);
        }

        $phpStanType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($functionLike->returnType);
        $phpDocInfo->changeReturnType($phpStanType);
    }

    /**
     * @param ClassMethod|Function_|Closure|ArrowFunction $functionLike
     */
    private function processParamTypes(FunctionLike $functionLike): void
    {
        foreach ($functionLike->getParams() as $param) {
            if (! $param->type instanceof UnionType) {
                continue;
            }

            /** @var PhpDocInfo|null $phpDocInfo */
            $phpDocInfo = $param->getAttribute(AttributeKey::PHP_DOC_INFO);
            if ($phpDocInfo === null) {
                $phpDocInfo = $this->phpDocInfoFactory->createEmpty($functionLike);
            }

            $phpStanType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
            $phpDocInfo->changeParamType($phpStanType, $param, $this->getName($param));
        }
    }
}
