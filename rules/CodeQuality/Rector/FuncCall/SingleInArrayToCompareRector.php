<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\FuncCall\SingleInArrayToCompareRector\SingleInArrayToCompareRectorTest
 */
final class SingleInArrayToCompareRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes in_array() with single element to ===', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        if (in_array(strtolower($type), ['$this'], true)) {
            return strtolower($type);
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        if (strtolower($type) === '$this') {
            return strtolower($type);
        }
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
        if (!$this->isName($node, 'in_array')) {
            return null;
        }
        if (!isset($node->args[1])) {
            return null;
        }
        if (!$node->args[1] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        if (!$node->args[1]->value instanceof \PhpParser\Node\Expr\Array_) {
            return null;
        }
        /** @var Array_ $arrayNode */
        $arrayNode = $node->args[1]->value;
        if (\count($arrayNode->items) !== 1) {
            return null;
        }
        $firstArrayItem = $arrayNode->items[0];
        if (!$firstArrayItem instanceof \PhpParser\Node\Expr\ArrayItem) {
            return null;
        }
        if (!isset($node->args[0])) {
            return null;
        }
        if (!$node->args[0] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $firstArrayItemValue = $firstArrayItem->value;
        // strict
        if (isset($node->args[2])) {
            return new \PhpParser\Node\Expr\BinaryOp\Identical($node->args[0]->value, $firstArrayItemValue);
        }
        return new \PhpParser\Node\Expr\BinaryOp\Equal($node->args[0]->value, $firstArrayItemValue);
    }
}
