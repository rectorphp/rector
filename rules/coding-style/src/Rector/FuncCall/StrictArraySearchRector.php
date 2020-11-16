<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Rector\AbstractRector;

/**
 * @see \Rector\CodingStyle\Tests\Rector\FuncCall\StrictArraySearchRector\StrictArraySearchRectorTest
 */
final class StrictArraySearchRector extends AbstractRector
{
    public function getRuleDefinition(): \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition(
            'Makes array_search search for identical elements',
            [
                new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(
                    'array_search($value, $items);',
                    'array_search($value, $items, true);'
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
        if (! $this->isName($node, 'array_search')) {
            return null;
        }

        if (count($node->args) === 2) {
            $node->args[2] = $this->createArg($this->createTrue());
        }

        return $node;
    }
}
