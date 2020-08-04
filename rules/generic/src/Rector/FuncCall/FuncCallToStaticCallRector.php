<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Generic\Tests\Rector\FuncCall\FuncCallToStaticCallRector\FuncCallToStaticCallRectorTest
 */
final class FuncCallToStaticCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const FUNC_CALLS_TO_STATIC_CALLS = 'func_calls_to_static_calls';

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
                    self::FUNC_CALLS_TO_STATIC_CALLS => [
                        'view' => ['SomeStaticClass', 'render'],
                    ],
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
        $this->functionToStaticCall = $configuration[self::FUNC_CALLS_TO_STATIC_CALLS] ?? [];
    }
}
