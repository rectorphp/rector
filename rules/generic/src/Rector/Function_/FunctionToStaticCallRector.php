<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\Function_;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Generic\Tests\Rector\Function_\FunctionToStaticCallRector\FunctionToStaticCallRectorTest
 */
final class FunctionToStaticCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const FUNCTION_TO_STATIC_CALL = '$functionToStaticCall';

    /**
     * @var string[]
     */
    private $functionToStaticCall = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns defined function call to static method call.', [
            new ConfiguredCodeSample(
                'view("...", []);',
                'SomeClass::render("...", []);',
                [
                    'view' => ['SomeStaticClass', 'render'],
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
        foreach ($this->functionToStaticCall as $function => $staticCall) {
            if (! $this->isName($node, $function)) {
                continue;
            }

            [$className, $methodName] = $staticCall;

            return $this->createStaticCall($className, $methodName, $node->args);
        }

        return null;
    }

    public function configure(array $configuration): void
    {
        $this->functionToStaticCall = $configuration[self::FUNCTION_TO_STATIC_CALL] ?? [];
    }
}
