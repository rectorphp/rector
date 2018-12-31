<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\FuncCall;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class SingleInArrayToCompareRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes in_array() with single element to ===', [
            new CodeSample(
                <<<'CODE_SAMPLE'
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
                ,
                <<<'CODE_SAMPLE'
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
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node, 'in_array')) {
            return null;
        }

        if (! $node->args[1]->value instanceof Array_) {
            return null;
        }

        /** @var Array_ $arrayNode */
        $arrayNode = $node->args[1]->value;
        if (count($arrayNode->items) !== 1) {
            return null;
        }

        $onlyArrayItem = $arrayNode->items[0]->value;
        if (isset($node->args[2])) { // strict
            return new Identical($node->args[0]->value, $onlyArrayItem);
        }

        return new Equal($node->args[0]->value, $onlyArrayItem);
    }
}
