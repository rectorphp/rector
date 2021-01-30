<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\CodeQuality\Tests\Rector\FuncCall\SimplifyFuncGetArgsCountRector\SimplifyFuncGetArgsCountRectorTest
 */
final class SimplifyFuncGetArgsCountRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Simplify count of func_get_args() to func_num_args()',
            [new CodeSample('count(func_get_args());', 'func_num_args();')]
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
        if (! $this->isName($node, 'count')) {
            return null;
        }

        if (! $node->args[0]->value instanceof FuncCall) {
            return null;
        }

        /** @var FuncCall $innerFuncCall */
        $innerFuncCall = $node->args[0]->value;

        if (! $this->isName($innerFuncCall, 'func_get_args')) {
            return $node;
        }

        return $this->nodeFactory->createFuncCall('func_num_args');
    }
}
