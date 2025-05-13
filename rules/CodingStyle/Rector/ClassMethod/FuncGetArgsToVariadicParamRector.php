<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector\FuncGetArgsToVariadicParamRectorTest
 */
final class FuncGetArgsToVariadicParamRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Refactor `func_get_args()` in to a variadic param', [new CodeSample(<<<'CODE_SAMPLE'
function run()
{
    $args = \func_get_args();
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function run(...$args)
{
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassMethod::class, Function_::class, Closure::class];
    }
    /**
     * @param ClassMethod|Function_|Closure $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->params !== [] || $node->stmts === null) {
            return null;
        }
        /** @var Expression<Assign>|null $expression */
        $expression = $this->matchFuncGetArgsVariableAssign($node);
        if (!$expression instanceof Expression) {
            return null;
        }
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if (!$stmt->expr instanceof Assign) {
                continue;
            }
            $assign = $stmt->expr;
            if (!$this->isFuncGetArgsFuncCall($assign->expr)) {
                continue;
            }
            if ($assign->var instanceof Variable) {
                /** @var string $variableName */
                $variableName = $this->getName($assign->var);
                unset($node->stmts[$key]);
                return $this->applyVariadicParams($node, $variableName);
            }
            $assign->expr = new Variable('args');
            return $this->applyVariadicParams($node, 'args');
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::VARIADIC_PARAM;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $node
     * @return \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|null
     */
    private function applyVariadicParams($node, string $variableName)
    {
        $param = $this->createVariadicParam($variableName);
        if ($param->var instanceof Variable && $this->hasFunctionOrClosureInside($node, $param->var)) {
            return null;
        }
        $node->params[] = $param;
        return $node;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $functionLike
     */
    private function hasFunctionOrClosureInside($functionLike, Variable $variable) : bool
    {
        if ($functionLike->stmts === null) {
            return \false;
        }
        return (bool) $this->betterNodeFinder->findFirst($functionLike->stmts, function (Node $node) use($variable) : bool {
            if (!$node instanceof Closure && !$node instanceof Function_) {
                return \false;
            }
            if ($node->params !== []) {
                return \false;
            }
            $expression = $this->matchFuncGetArgsVariableAssign($node);
            if (!$expression instanceof Expression) {
                return \false;
            }
            /** @var Assign $assign */
            $assign = $expression->expr;
            return $this->nodeComparator->areNodesEqual($assign->var, $variable);
        });
    }
    /**
     * @return Expression<Assign>|null
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $functionLike
     */
    private function matchFuncGetArgsVariableAssign($functionLike) : ?Expression
    {
        /** @var Expression[] $expressions */
        $expressions = $this->betterNodeFinder->findInstancesOfInFunctionLikeScoped($functionLike, Expression::class);
        foreach ($expressions as $expression) {
            if (!$expression->expr instanceof Assign) {
                continue;
            }
            $assign = $expression->expr;
            if (!$assign->expr instanceof FuncCall) {
                continue;
            }
            if (!$this->isName($assign->expr, 'func_get_args')) {
                continue;
            }
            return $expression;
        }
        return null;
    }
    private function createVariadicParam(string $variableName) : Param
    {
        $variable = new Variable($variableName);
        return new Param($variable, null, null, \false, \true);
    }
    private function isFuncGetArgsFuncCall(Expr $expr) : bool
    {
        if (!$expr instanceof FuncCall) {
            return \false;
        }
        return $this->isName($expr, 'func_get_args');
    }
}
