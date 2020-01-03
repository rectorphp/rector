<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://stackoverflow.com/questions/559844/whats-better-to-use-in-php-array-value-or-array-pusharray-value
 *
 * @see \Rector\CodeQuality\Tests\Rector\FuncCall\ChangeArrayPushToArrayAssignRector\ChangeArrayPushToArrayAssignRectorTest
 */
final class ChangeArrayPushToArrayAssignRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change array_push() to direct variable assign', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $items = [];
        array_push($items, $item);
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $items = [];
        $items[] = $item;
    }
}
PHP

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
        if (! $this->isName($node, 'array_push')) {
            return null;
        }

        $arrayDimFetch = new Node\Expr\ArrayDimFetch($node->args[0]->value);
        return new Assign($arrayDimFetch, $node->args[1]->value);
    }
}
