<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Generic\Tests\Rector\MethodCall\MethodCallToStaticCallRector\MethodCallToStaticCallRectorTest
 */
final class MethodCallToStaticCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const METHOD_CALLS_TO_STATIC_CALLS = 'method_calls_to_static_calls';

    /**
     * @var array<string, array<string, string>>
     */
    private $methodCallsToStaticCalls = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change method call to desired static call', [
            new ConfiguredCodeSample(
                <<<'PHP'
final class SomeClass
{
    private $anotherDependency;

    public function __construct(AnotherDependency $anotherDependency)
    {
        $this->anotherDependency = $anotherDependency;
    }

    public function loadConfiguration()
    {
        return $this->anotherDependency->process('value');
    }
}
PHP
,
                <<<'PHP'
final class SomeClass
{
    private $anotherDependency;

    public function __construct(AnotherDependency $anotherDependency)
    {
        $this->anotherDependency = $anotherDependency;
    }

    public function loadConfiguration()
    {
        return StaticCaller::anotherMethod('value');
    }
}
PHP
,
                [
                    self::METHOD_CALLS_TO_STATIC_CALLS => [
                        'AnotherDependency' => [
                            'process' => ['StaticCaller', 'anotherMethod'],
                        ],
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
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->methodCallsToStaticCalls === []) {
            return null;
        }

        foreach ($this->methodCallsToStaticCalls as $class => $staticCallsByMethod) {
            if (! $this->isObjectType($node->var, $class)) {
                continue;
            }

            foreach ($staticCallsByMethod as $method => $staticCall) {
                if (! $this->isName($node->name, $method)) {
                    continue;
                }

                return $this->createStaticCall($staticCall[0], $staticCall[1], $node->args);
            }
        }

        return null;
    }

    public function configure(array $configuration): void
    {
        $this->methodCallsToStaticCalls = $configuration[self::METHOD_CALLS_TO_STATIC_CALLS] ?? [];
    }
}
