<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\MethodCall;

use PhpParser\Node;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
_Source_
 * @see \Rector\Nette\Tests\Rector\MethodCall\TranslateClassMethodToVariadicsRector\TranslateClassMethodToVariadicsRectorTest
 */
final class TranslateClassMethodToVariadicsRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change translate() method call 2nd arg to variadic', [
            new CodeSample(
                <<<'PHP'
use Nette\Localization\ITranslator;

final class SomeClass implements ITranslator
{
    public function translate($message, $count = null)
    {
        return [$message, $count];
    }
}
PHP
,
                <<<'PHP'
use Nette\Localization\ITranslator;

final class SomeClass implements ITranslator
{
    public function translate($message, ... $parameters)
    {
        $count = $parameters[0] ?? null;
        return [$message, $count];
    }
}
PHP

            )
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }

    /**
     * @param \PhpParser\Node\Expr\MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        // change the node

        return $node;
    }
}
