<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\NodeAnalyzer\CallAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class SimplifyInArrayValuesRector extends AbstractRector
{
    /**
     * @var CallAnalyzer
     */
    private $callAnalyzer;

    public function __construct(CallAnalyzer $callAnalyzer)
    {
        $this->callAnalyzer = $callAnalyzer;
    }

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
        // @todo shorten to "isName()" trait
        if (! $this->callAnalyzer->isName($node, 'in_array')) {
            return $node;
        }

        if (! $node->args[1]->value instanceof FuncCall) {
            return $node;
        }

        /** @var FuncCall $innerFunCall */
        $innerFunCall = $node->args[1]->value;
        if (! $this->callAnalyzer->isName($innerFunCall, 'array_values')) {
            return $node;
        }

        $node->args[1] = $innerFunCall->args[0];

        return $node;
    }
}
