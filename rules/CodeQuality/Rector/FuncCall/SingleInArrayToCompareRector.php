<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\FuncCall\SingleInArrayToCompareRector\SingleInArrayToCompareRectorTest
 */
final class SingleInArrayToCompareRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes in_array() with single element to ===', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [BooleanNot::class, FuncCall::class];
    }
    /**
     * @param BooleanNot|FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof BooleanNot) {
            if (!$node->expr instanceof FuncCall) {
                return null;
            }
            $firstArrayItem = $this->resolveArrayItem($node->expr);
            if (!$firstArrayItem instanceof ArrayItem) {
                return null;
            }
            return $this->processCompare($firstArrayItem, $node->expr, \true);
        }
        $firstArrayItem = $this->resolveArrayItem($node);
        if (!$firstArrayItem instanceof ArrayItem) {
            return null;
        }
        return $this->processCompare($firstArrayItem, $node);
    }
    private function resolveArrayItem(FuncCall $funcCall) : ?ArrayItem
    {
        if (!$this->isName($funcCall, 'in_array')) {
            return null;
        }
        if ($funcCall->isFirstClassCallable()) {
            return null;
        }
        if (!isset($funcCall->args[1])) {
            return null;
        }
        if (!$funcCall->args[1] instanceof Arg) {
            return null;
        }
        if (!$funcCall->args[1]->value instanceof Array_) {
            return null;
        }
        /** @var Array_ $arrayNode */
        $arrayNode = $funcCall->args[1]->value;
        if (\count($arrayNode->items) !== 1) {
            return null;
        }
        $firstArrayItem = $arrayNode->items[0];
        if (!$firstArrayItem instanceof ArrayItem) {
            return null;
        }
        if ($firstArrayItem->unpack) {
            return null;
        }
        if (!isset($funcCall->getArgs()[0])) {
            return null;
        }
        return $firstArrayItem;
    }
    private function processCompare(ArrayItem $firstArrayItem, FuncCall $funcCall, bool $isNegated = \false) : Node
    {
        $firstArrayItemValue = $firstArrayItem->value;
        $firstArg = $funcCall->getArgs()[0];
        // strict
        if (isset($funcCall->getArgs()[2])) {
            return $isNegated ? new NotIdentical($firstArg->value, $firstArrayItemValue) : new Identical($firstArg->value, $firstArrayItemValue);
        }
        return $isNegated ? new NotEqual($firstArg->value, $firstArrayItemValue) : new Equal($firstArg->value, $firstArrayItemValue);
    }
}
