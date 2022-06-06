<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DowngradePhp70\Rector\FunctionLike;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\Cast;
use RectorPrefix20220606\PhpParser\Node\Expr\Cast\Bool_;
use RectorPrefix20220606\PhpParser\Node\Expr\Cast\Double;
use RectorPrefix20220606\PhpParser\Node\Expr\Cast\Int_;
use RectorPrefix20220606\PhpParser\Node\Expr\Cast\String_;
use RectorPrefix20220606\PhpParser\Node\Expr\Closure;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
use RectorPrefix20220606\PhpParser\Node\Stmt\Function_;
use RectorPrefix20220606\PHPStan\Type\BooleanType;
use RectorPrefix20220606\PHPStan\Type\FloatType;
use RectorPrefix20220606\PHPStan\Type\IntegerType;
use RectorPrefix20220606\PHPStan\Type\StringType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocParser\PhpDocFromTypeDeclarationDecorator;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/scalar_type_hints
 *
 * @see \Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeScalarTypeDeclarationRector\DowngradeScalarTypeDeclarationRectorTest
 */
final class DowngradeScalarTypeDeclarationRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocParser\PhpDocFromTypeDeclarationDecorator
     */
    private $phpDocFromTypeDeclarationDecorator;
    public function __construct(PhpDocFromTypeDeclarationDecorator $phpDocFromTypeDeclarationDecorator)
    {
        $this->phpDocFromTypeDeclarationDecorator = $phpDocFromTypeDeclarationDecorator;
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
        return new RuleDefinition('Remove the type params and return type, add @param and @return tags instead', [new CodeSample(<<<'CODE_SAMPLE'
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
    public function refactor(Node $node) : ?Node
    {
        $recastAssigns = [];
        foreach ($node->params as $param) {
            if ($param->type === null) {
                continue;
            }
            $this->phpDocFromTypeDeclarationDecorator->decorateParam($param, $node, [StringType::class, IntegerType::class, BooleanType::class, FloatType::class]);
            $recastAssign = $this->resolveRecastAssign($param, $node);
            if ($recastAssign instanceof Expression) {
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
    private function resolveRecastAssign(Param $param, $functionLike) : ?Expression
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
        $variable = new Variable($paramName);
        $paramType = $this->getType($param);
        $recastedVariable = $this->recastVariabletIfScalarType($variable, $paramType);
        if (!$recastedVariable instanceof Cast) {
            return null;
        }
        $assign = new Assign($variable, $recastedVariable);
        return new Expression($assign);
    }
    private function recastVariabletIfScalarType(Variable $variable, Type $type) : ?Cast
    {
        if ($type instanceof StringType) {
            return new String_($variable);
        }
        if ($type instanceof IntegerType) {
            return new Int_($variable);
        }
        if ($type instanceof FloatType) {
            return new Double($variable);
        }
        if ($type instanceof BooleanType) {
            return new Bool_($variable);
        }
        return null;
    }
}
