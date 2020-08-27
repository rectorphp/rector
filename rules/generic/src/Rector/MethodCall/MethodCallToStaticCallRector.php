<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Generic\ValueObject\MethodCallToStaticCall;
use Webmozart\Assert\Assert;

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
     * @var MethodCallToStaticCall[]
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
                        new MethodCallToStaticCall('AnotherDependency', 'process', 'StaticCaller', 'anotherMethod'),
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
        foreach ($this->methodCallsToStaticCalls as $methodCallToStaticCall) {
            if (! $this->isObjectType($node->var, $methodCallToStaticCall->getOldClass())) {
                continue;
            }

            if (! $this->isName($node->name, $methodCallToStaticCall->getOldMethod())) {
                continue;
            }

            return $this->createStaticCall(
                $methodCallToStaticCall->getNewClass(),
                $methodCallToStaticCall->getNewMethod(),
                $node->args
            );
        }

        return null;
    }

    public function configure(array $configuration): void
    {
        $methodCallsToStaticCalls = $configuration[self::METHOD_CALLS_TO_STATIC_CALLS] ?? [];
        Assert::allIsInstanceOf($methodCallsToStaticCalls, MethodCallToStaticCall::class);
        $this->methodCallsToStaticCalls = $methodCallsToStaticCalls;
    }
}
