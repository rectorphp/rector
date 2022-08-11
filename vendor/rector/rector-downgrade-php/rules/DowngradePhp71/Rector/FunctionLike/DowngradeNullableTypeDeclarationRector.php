<?php

declare (strict_types=1);
namespace Rector\DowngradePhp71\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeAnalyzer\ParamAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\PhpDocDecorator\PhpDocFromTypeDeclarationDecorator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp71\Rector\FunctionLike\DowngradeNullableTypeDeclarationRector\DowngradeNullableTypeDeclarationRectorTest
 */
final class DowngradeNullableTypeDeclarationRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\PhpDocDecorator\PhpDocFromTypeDeclarationDecorator
     */
    private $phpDocFromTypeDeclarationDecorator;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ParamAnalyzer
     */
    private $paramAnalyzer;
    public function __construct(PhpDocTypeChanger $phpDocTypeChanger, PhpDocFromTypeDeclarationDecorator $phpDocFromTypeDeclarationDecorator, ParamAnalyzer $paramAnalyzer)
    {
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->phpDocFromTypeDeclarationDecorator = $phpDocFromTypeDeclarationDecorator;
        $this->paramAnalyzer = $paramAnalyzer;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Function_::class, ClassMethod::class, Closure::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove the nullable type params, add @param tags instead', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(?string $input): ?string
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @param string|null $input
     * @return string|null
     */
    public function run($input)
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param ClassMethod|Function_|Closure $node
     */
    public function refactor(Node $node) : ?Node
    {
        $hasChanged = \false;
        foreach ($node->params as $param) {
            if ($this->refactorParamType($param, $node)) {
                $hasChanged = \true;
            }
        }
        if ($node->returnType instanceof NullableType) {
            $this->phpDocFromTypeDeclarationDecorator->decorateReturn($node);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $functionLike
     */
    private function refactorParamType(Param $param, $functionLike) : bool
    {
        if (!$this->paramAnalyzer->isNullable($param)) {
            return \false;
        }
        $this->decorateWithDocBlock($functionLike, $param);
        $param->type = null;
        return \true;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $functionLike
     */
    private function decorateWithDocBlock($functionLike, Param $param) : void
    {
        if ($param->type === null) {
            return;
        }
        $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        $paramName = $this->getName($param->var);
        if ($paramName === null) {
            throw new ShouldNotHappenException();
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($functionLike);
        $this->phpDocTypeChanger->changeParamType($phpDocInfo, $type, $param, $paramName);
    }
}
