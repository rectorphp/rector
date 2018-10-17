<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class SimplifyStrposLowerRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Simplify strpos(strtolower(), "...") calls',
            [new CodeSample('strpos(strtolower($var), "...")"', 'stripos($var, "...")"')]
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
     * @param FuncCall $node;
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node, 'strpos')) {
            return $node;
        }

        if (! isset($node->args[0])) {
            return $node;
        }

        if (! $node->args[0]->value instanceof FuncCall) {
            return $node;
        }

        /** @var FuncCall $innerFuncCall */
        $innerFuncCall = $node->args[0]->value;
        if (! $this->isName($innerFuncCall, 'strtolower')) {
            return $node;
        }

        // pop 1 level up
        $node->args[0] = $innerFuncCall->args[0];

        $node->name = new Name('stripos');

        return $node;
    }
}
