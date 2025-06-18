<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Ternary\TernaryImplodeToImplodeRector\TernaryImplodeToImplodeRectorTest
 */
final class TernaryImplodeToImplodeRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Narrow ternary with implode and empty string to direct implode, as same result', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(array $values)
    {
        return $values === [] ? '' : implode(',', $values);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(array $values)
    {
        return implode(',', $values);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Ternary::class];
    }
    /**
     * @param Ternary $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$node->cond instanceof Identical) {
            return null;
        }
        $identical = $node->cond;
        if (!$identical->right instanceof Array_) {
            return null;
        }
        if ($identical->right->items !== []) {
            return null;
        }
        if (!$node->if instanceof String_) {
            return null;
        }
        if ($node->if->value !== '') {
            return null;
        }
        if (!$node->else instanceof FuncCall) {
            return null;
        }
        if (!$this->isNames($node->else, ['implode', 'join'])) {
            return null;
        }
        if ($node->else->isFirstClassCallable()) {
            return null;
        }
        $function = $node->else;
        $secondArg = $function->getArgs()[1] ?? null;
        if (!$secondArg instanceof Arg) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($identical->left, $secondArg->value)) {
            return null;
        }
        return $node->else;
    }
}
