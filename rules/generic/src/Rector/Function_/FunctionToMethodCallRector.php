<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\Function_;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Generic\Tests\Rector\Function_\FunctionToMethodCallRector\FunctionToMethodCallRectorTest
 */
final class FunctionToMethodCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const FUNCTION_TO_METHOD_CALL = '$functionToMethodCall';

    /**
     * @var string[]
     */
    private $functionToMethodCall = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns defined function calls to local method calls.', [
            new ConfiguredCodeSample(
                'view("...", []);',
                '$this->render("...", []);',
                [
                    'view' => ['this', 'render'],
                ]
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
        if (! $node->name instanceof Name) {
            return null;
        }

        $functionName = $this->getName($node);

        if (! isset($this->functionToMethodCall[$functionName])) {
            return null;
        }

        /** @var Identifier $identifier */
        $identifier = $node->name;
        $functionName = $identifier->toString();

        [$variableName, $methodName] = $this->functionToMethodCall[$functionName];

        return $this->createMethodCall($variableName, $methodName, $node->args);
    }

    public function configure(array $configuration): void
    {
        $this->functionToMethodCall = $configuration[self::FUNCTION_TO_METHOD_CALL] ?? [];
    }
}
