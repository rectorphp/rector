<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class InArrayAndArrayKeysToArrayKeyExistsRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Simplify `in_array` and `array_keys` functions combination into `array_key_exists` when `array_keys` has one argument only',
            [new CodeSample('in_array("key", array_keys($array), true);', 'array_key_exists("key", $array);')]
        );
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

        $secondArgument = $node->args[1]->value;
        if (! $secondArgument instanceof FuncCall) {
            return null;
        }

        if (! $this->isName($secondArgument, 'array_keys')) {
            return null;
        }

        if (count($secondArgument->args) > 1) {
            return null;
        }

        [$key, $array] = $node->args;

        /** @var FuncCall $innerFuncCallNode */
        $innerFuncCallNode = $array->value;
        $array = $innerFuncCallNode->args[0];

        $node->name = new Name('array_key_exists');
        $node->args = [$key, $array];

        return $node;
    }
}
