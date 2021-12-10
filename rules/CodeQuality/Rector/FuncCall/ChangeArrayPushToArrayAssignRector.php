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
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://stackoverflow.com/questions/559844/whats-better-to-use-in-php-array-value-or-array-pusharray-value
 *
 * @see \Rector\Tests\CodeQuality\Rector\FuncCall\ChangeArrayPushToArrayAssignRector\ChangeArrayPushToArrayAssignRectorTest
 */
final class ChangeArrayPushToArrayAssignRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change array_push() to direct variable assign', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $items = [];
        array_push($items, $item);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $items = [];
        $items[] = $item;
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
        return [\PhpParser\Node\Expr\FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isName($node, 'array_push')) {
            return null;
        }
        if ($this->hasArraySpread($node)) {
            return null;
        }
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parent instanceof \PhpParser\Node\Stmt\Expression) {
            return null;
        }
        if (!isset($node->args[0])) {
            return null;
        }
        if (!$node->args[0] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $arrayDimFetch = new \PhpParser\Node\Expr\ArrayDimFetch($node->args[0]->value);
        $position = 1;
        while (isset($node->args[$position]) && $node->args[$position] instanceof \PhpParser\Node\Arg) {
            $assign = new \PhpParser\Node\Expr\Assign($arrayDimFetch, $node->args[$position]->value);
            $assignExpression = new \PhpParser\Node\Stmt\Expression($assign);
            // keep comments of first line
            if ($position === 1) {
                $this->mirrorComments($assignExpression, $node);
            }
            $this->nodesToAddCollector->addNodeAfterNode($assignExpression, $node);
            ++$position;
        }
        $this->removeNode($node);
        return null;
    }
    private function hasArraySpread(\PhpParser\Node\Expr\FuncCall $funcCall) : bool
    {
        foreach ($funcCall->args as $arg) {
            /** @var Arg $arg */
            if ($arg->unpack) {
                return \true;
            }
        }
        return \false;
    }
}
