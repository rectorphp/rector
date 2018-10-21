<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class SimplifyInArrayValuesRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Removes unneeded array_values() in in_array() call', [
            new CodeSample('in_array("key", array_values($array), true);', 'in_array("key", $array, true);'),
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

        if (! $node->args[1]->value instanceof FuncCall) {
            return null;
        }

        /** @var FuncCall $innerFunCall */
        $innerFunCall = $node->args[1]->value;
        if (! $this->isName($innerFunCall, 'array_values')) {
            return null;
        }

        $node->args[1] = $innerFunCall->args[0];

        return $node;
    }
}
