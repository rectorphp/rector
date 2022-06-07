<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://stackoverflow.com/questions/559844/whats-better-to-use-in-php-array-value-or-array-pusharray-value
 *
 * @see \Rector\Tests\CodeQuality\Rector\FuncCall\ChangeArrayPushToArrayAssignRector\ChangeArrayPushToArrayAssignRectorTest
 */
final class ChangeArrayPushToArrayAssignRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change array_push() to direct variable assign', [new CodeSample(<<<'CODE_SAMPLE'
$items = [];
array_push($items, $item);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$items = [];
$items[] = $item;
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Expression::class];
    }
    /**
     * @param Expression[] $node
     * @param Expression[]|null $node
     */
    public function refactor(Node $node) : ?array
    {
        if (!$node->expr instanceof FuncCall) {
            return null;
        }
        $funcCall = $node->expr;
        if (!$this->isName($funcCall, 'array_push')) {
            return null;
        }
        if ($this->hasArraySpread($funcCall)) {
            return null;
        }
        $args = $funcCall->getArgs();
        if ($args === []) {
            return null;
        }
        /** @var Arg $firstArg */
        $firstArg = \array_shift($args);
        if ($args === []) {
            return null;
        }
        $arrayDimFetch = new ArrayDimFetch($firstArg->value);
        $newStmts = [];
        foreach ($args as $key => $arg) {
            $assign = new Assign($arrayDimFetch, $arg->value);
            $assignExpression = new Expression($assign);
            $newStmts[] = $assignExpression;
            // keep comments of first line
            if ($key === 0) {
                $this->mirrorComments($assignExpression, $node);
            }
        }
        return $newStmts;
    }
    private function hasArraySpread(FuncCall $funcCall) : bool
    {
        foreach ($funcCall->getArgs() as $arg) {
            if ($arg->unpack) {
                return \true;
            }
        }
        return \false;
    }
}
