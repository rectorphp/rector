<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
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
final class FuncGetArgsToVariadicParamRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Refactor func_get_args() in to a variadic param', [
            new CodeSample(
                <<<'CODE_SAMPLE'
function run()
{
    $args = \func_get_args();
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
function run(...$args)
{
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Function_::class];
    }

    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::VARIADIC_PARAM)) {
            return null;
        }

        if ($node->params !== []) {
            return null;
        }

        $assign = $this->matchFuncGetArgsVariableAssign($node);
        if (! $assign instanceof Assign) {
            return null;
        }

        if ($assign->var instanceof Variable) {
            $variableName = $this->getName($assign->var);
            if ($variableName === null) {
                return null;
            }

            $this->removeNode($assign);
        } else {
            $variableName = 'args';
            $assign->expr = new Variable('args');
        }

        $node->params[] = $this->createVariadicParam($variableName);

        return $node;
    }

    private function matchFuncGetArgsVariableAssign(ClassMethod | Function_ $functionLike): ?Assign
    {
        /** @var Assign[] $assigns */
        $assigns = $this->betterNodeFinder->findInstanceOf((array) $functionLike->stmts, Assign::class);

        foreach ($assigns as $assign) {
            if (! $assign->expr instanceof FuncCall) {
                continue;
            }

            if (! $this->isName($assign->expr, 'func_get_args')) {
                continue;
            }

            return $assign;
        }

        return null;
    }

    private function createVariadicParam(string $variableName): Param
    {
        $variable = new Variable($variableName);
        return new Param($variable, null, null, false, true);
    }
}
