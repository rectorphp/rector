<?php

declare(strict_types=1);

namespace Rector\DowngradePhp70\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\ArrayType;
use PHPStan\Type\CallableType;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DowngradePhp70\Tests\Rector\FunctionLike\DowngradeTypeDeclarationRector\DowngradeTypeDeclarationRectorTest
 */
final class DowngradeTypeDeclarationRector extends AbstractRector
{
    /**
     * @var PhpDocTypeChanger
     */
    private $phpDocTypeChanger;

    public function __construct(PhpDocTypeChanger $phpDocTypeChanger)
    {
        $this->phpDocTypeChanger = $phpDocTypeChanger;
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Function_::class, ClassMethod::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove the type params and return type, add @param and @return tags instead',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(string $input): string
    {
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @param string $input
     * @return string
     */
    public function run($input)
    {
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @param Function_|ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        $this->refactorParams($node);
        $this->refactorReturn($node);

        return $node;
    }

    /**
     * @param ClassMethod|Function_ $functionLike
     */
    private function refactorParams(FunctionLike $functionLike): void
    {
        foreach ($functionLike->params as $param) {
            if ($param->type === null) {
                continue;
            }

            // only 2 allowed types
            $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
            if (is_a($type, ArrayType::class, true)) {
                continue;
            }

            if (is_a($type, CallableType::class, true)) {
                continue;
            }

            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($functionLike);
            $paramName = $this->nodeNameResolver->getName($param);
            $this->phpDocTypeChanger->changeParamType($phpDocInfo, $type, $param, $paramName);

            $param->type = null;
        }
    }

    /**
     * @param ClassMethod|Function_ $functionLike
     */
    private function refactorReturn(FunctionLike $functionLike): void
    {
        if ($functionLike->returnType === null) {
            return;
        }

        $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($functionLike->returnType);
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($functionLike);
        $this->phpDocTypeChanger->changeReturnType($phpDocInfo, $type);

        $functionLike->returnType = null;
    }
}
