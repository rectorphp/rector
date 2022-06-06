<?php

declare (strict_types=1);
namespace Rector\DowngradePhp70\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\Cast\Double;
use PhpParser\Node\Expr\Cast\Int_;
use PhpParser\Node\Expr\Cast\String_;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocParser\PhpDocFromTypeDeclarationDecorator;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/scalar_type_hints
 *
 * @see \Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeScalarTypeDeclarationRector\DowngradeScalarTypeDeclarationRectorTest
 */
final class DowngradeScalarTypeDeclarationRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocParser\PhpDocFromTypeDeclarationDecorator
     */
    private $phpDocFromTypeDeclarationDecorator;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocParser\PhpDocFromTypeDeclarationDecorator $phpDocFromTypeDeclarationDecorator)
    {
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
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove the type params and return type, add @param and @return tags instead', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(string $input): string
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
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
)]);
    }
    /**
     * @param Function_|ClassMethod|Closure $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $recastAssigns = [];
        foreach ($node->params as $param) {
            if ($param->type === null) {
                continue;
            }
            $this->phpDocFromTypeDeclarationDecorator->decorateParam($param, $node, [\PHPStan\Type\StringType::class, \PHPStan\Type\IntegerType::class, \PHPStan\Type\BooleanType::class, \PHPStan\Type\FloatType::class]);
            $recastAssign = $this->resolveRecastAssign($param, $node);
            if ($recastAssign instanceof \PhpParser\Node\Stmt\Expression) {
                $recastAssigns[] = $recastAssign;
            }
        }
        if ($recastAssigns !== []) {
            $node->stmts = \array_merge($recastAssigns, (array) $node->stmts);
        }
        if ($node->returnType === null) {
            return null;
        }
        $this->phpDocFromTypeDeclarationDecorator->decorate($node);
        return $node;
    }
    /**
     * @param \PhpParser\Node\Stmt\Function_|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Expr\Closure $functionLike
     */
    private function resolveRecastAssign(\PhpParser\Node\Param $param, $functionLike) : ?\PhpParser\Node\Stmt\Expression
    {
        if ($functionLike->stmts === null) {
            return null;
        }
        if ($functionLike->stmts === []) {
            return null;
        }
        // add possible object with __toString() re-type to keep original behavior
        // @see https://twitter.com/VotrubaT/status/1390974218108538887
        /** @var string $paramName */
        $paramName = $this->getName($param->var);
        $variable = new \PhpParser\Node\Expr\Variable($paramName);
        $paramType = $this->getType($param);
        $recastedVariable = $this->recastVariabletIfScalarType($variable, $paramType);
        if (!$recastedVariable instanceof \PhpParser\Node\Expr\Cast) {
            return null;
        }
        $assign = new \PhpParser\Node\Expr\Assign($variable, $recastedVariable);
        return new \PhpParser\Node\Stmt\Expression($assign);
    }
    private function recastVariabletIfScalarType(\PhpParser\Node\Expr\Variable $variable, \PHPStan\Type\Type $type) : ?\PhpParser\Node\Expr\Cast
    {
        if ($type instanceof \PHPStan\Type\StringType) {
            return new \PhpParser\Node\Expr\Cast\String_($variable);
        }
        if ($type instanceof \PHPStan\Type\IntegerType) {
            return new \PhpParser\Node\Expr\Cast\Int_($variable);
        }
        if ($type instanceof \PHPStan\Type\FloatType) {
            return new \PhpParser\Node\Expr\Cast\Double($variable);
        }
        if ($type instanceof \PHPStan\Type\BooleanType) {
            return new \PhpParser\Node\Expr\Cast\Bool_($variable);
        }
        return null;
    }
}
