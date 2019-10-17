<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodeQuality\Tests\Rector\FuncCall\AddPregQuoteDelimiterRector\AddPregQuoteDelimiterRectorTest
 */
final class AddPregQuoteDelimiterRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Add preg_quote delimiter when missing', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function test()
    {
        return preg_quote('name');
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function test()
    {
        return preg_quote('name', '#');
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
        if (! $this->isName($node, 'preg_quote')) {
            return null;
        }

        // already completed
        if (isset($node->args[1])) {
            return null;
        }

        $node->args[1] = new Node\Arg(new Node\Scalar\String_('#'));

        return $node;
    }
}
