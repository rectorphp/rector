<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://3v4l.org/d4tBd
 *
 * @see \Rector\Tests\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector\FuncGetArgsToVariadicParamRectorTest
 */
final class FuncGetArgsToVariadicParamRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Refactor func_get_args() in to a variadic param', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Function_::class];
    }
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::VARIADIC_PARAM)) {
            return null;
        }
        if ($node->params !== []) {
            return null;
        }
        $assign = $this->matchFuncGetArgsVariableAssign($node);
        if (!$assign instanceof \PhpParser\Node\Expr\Assign) {
            return null;
        }
        if ($assign->var instanceof \PhpParser\Node\Expr\Variable) {
            $variableName = $this->getName($assign->var);
            if ($variableName === null) {
                return null;
            }
            $this->removeNode($assign);
        } else {
            $variableName = 'args';
            $assign->expr = new \PhpParser\Node\Expr\Variable('args');
        }
        $node->params[] = $this->createVariadicParam($variableName);
        return $node;
    }
    /**
     * @param ClassMethod|Function_ $functionLike
     */
    private function matchFuncGetArgsVariableAssign(\PhpParser\Node\FunctionLike $functionLike) : ?\PhpParser\Node\Expr\Assign
    {
        /** @var Assign[] $assigns */
        $assigns = $this->betterNodeFinder->findInstanceOf((array) $functionLike->stmts, \PhpParser\Node\Expr\Assign::class);
        foreach ($assigns as $assign) {
            if (!$assign->expr instanceof \PhpParser\Node\Expr\FuncCall) {
                continue;
            }
            if (!$this->isName($assign->expr, 'func_get_args')) {
                continue;
            }
            return $assign;
        }
        return null;
    }
    private function createVariadicParam(string $variableName) : \PhpParser\Node\Param
    {
        $variable = new \PhpParser\Node\Expr\Variable($variableName);
        return new \PhpParser\Node\Param($variable, null, null, \false, \true);
    }
}
