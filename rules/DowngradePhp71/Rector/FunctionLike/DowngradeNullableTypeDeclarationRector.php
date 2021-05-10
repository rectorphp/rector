<?php

declare (strict_types=1);
namespace Rector\DowngradePhp71\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\DowngradePhp71\TypeDeclaration\PhpDocFromTypeDeclarationDecorator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp71\Rector\FunctionLike\DowngradeNullableTypeDeclarationRector\DowngradeNullableTypeDeclarationRectorTest
 */
final class DowngradeNullableTypeDeclarationRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @var \Rector\DowngradePhp71\TypeDeclaration\PhpDocFromTypeDeclarationDecorator
     */
    private $phpDocFromTypeDeclarationDecorator;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger $phpDocTypeChanger, \Rector\DowngradePhp71\TypeDeclaration\PhpDocFromTypeDeclarationDecorator $phpDocFromTypeDeclarationDecorator)
    {
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->phpDocFromTypeDeclarationDecorator = $phpDocFromTypeDeclarationDecorator;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Function_::class, \PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Expr\Closure::class];
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove the nullable type params, add @param tags instead', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
     * @return string|null $input
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
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $hasChanged = \false;
        foreach ($node->params as $param) {
            if ($this->refactorParamType($param, $node)) {
                $hasChanged = \true;
            }
        }
        if ($node->returnType instanceof \PhpParser\Node\NullableType && $this->phpDocFromTypeDeclarationDecorator->decorate($node)) {
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function isNullableParam(\PhpParser\Node\Param $param) : bool
    {
        if ($param->variadic) {
            return \false;
        }
        if ($param->type === null) {
            return \false;
        }
        // Check it is the union type
        return $param->type instanceof \PhpParser\Node\NullableType;
    }
    /**
     * @param ClassMethod|Function_|Closure $functionLike
     */
    private function refactorParamType(\PhpParser\Node\Param $param, \PhpParser\Node\FunctionLike $functionLike) : bool
    {
        if (!$this->isNullableParam($param)) {
            return \false;
        }
        $this->decorateWithDocBlock($functionLike, $param);
        $param->type = null;
        return \true;
    }
    /**
     * @param ClassMethod|Function_|Closure $functionLike
     */
    private function decorateWithDocBlock(\PhpParser\Node\FunctionLike $functionLike, \PhpParser\Node\Param $param) : void
    {
        if ($param->type === null) {
            return;
        }
        $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        $paramName = $this->getName($param->var);
        if ($paramName === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($functionLike);
        $this->phpDocTypeChanger->changeParamType($phpDocInfo, $type, $param, $paramName);
    }
}
