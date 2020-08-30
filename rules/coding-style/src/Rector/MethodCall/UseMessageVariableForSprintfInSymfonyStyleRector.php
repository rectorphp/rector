<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\MethodCall\UseMessageVariableForSprintfInSymfonyStyleRector\UseMessageVariableForSprintfInSymfonyStyleRectorTest
 */
final class UseMessageVariableForSprintfInSymfonyStyleRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Decouple $message property from sprintf() calls in $this->smyfonyStyle->method()',
            [
                new CodeSample(
                    <<<'PHP'
use Symfony\Component\Console\Style\SymfonyStyle;

final class SomeClass
{
    public function run(SymfonyStyle $symfonyStyle)
    {
        $symfonyStyle->info(sprintf('Hi %s', 'Tom'));
    }
}
PHP
,
                    <<<'PHP'
use Symfony\Component\Console\Style\SymfonyStyle;

final class SomeClass
{
    public function run(SymfonyStyle $symfonyStyle)
    {
        $message = sprintf('Hi %s', 'Tom');
        $symfonyStyle->info($message);
    }
}
PHP
            ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isObjectType($node, 'Symfony\Component\Console\Style\SymfonyStyle')) {
            return null;
        }

        if (! isset($node->args[0])) {
            return null;
        }

        $argValue = $node->args[0]->value;
        if (! $this->isFuncCallName($argValue, 'sprintf')) {
            return null;
        }

        $messageVariable = new Variable('message');
        $assign = new Assign($messageVariable, $argValue);
        $this->addNodeBeforeNode($assign, $node);

        $node->args[0]->value = $messageVariable;

        return $node;
    }
}
