<?php

declare(strict_types=1);

namespace Rector\Php54\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Php54\Tests\Rector\FuncCall\RemoveReferenceFromCallRector\RemoveReferenceFromCallRectorTest
 */
final class RemoveReferenceFromCallRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove & from function and method calls', [
            new CodeSample(
                <<<'PHP'
final class SomeClass
{
    public function run($one)
    {
        return strlen(&$one);
    }
}
PHP
                ,
                <<<'PHP'
final class SomeClass
{
    public function run($one)
    {
        return strlen($one);
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
        foreach ($node->args as $nodeArg) {
            if ($nodeArg->byRef) {
                $nodeArg->byRef = false;
            }
        }

        return $node;
    }
}
