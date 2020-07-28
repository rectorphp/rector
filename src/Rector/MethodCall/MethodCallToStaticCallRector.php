<?php

declare(strict_types=1);

namespace Rector\Core\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Core\Tests\Rector\MethodCall\MethodCallToStaticCallRector\MethodCallToStaticCallRectorTest
 */
final class MethodCallToStaticCallRector extends AbstractRector
{
    /**
     * @var mixed[]
     */
    private $methodCallsToStaticCalls;

    public function __construct(array $methodCallsToStaticCalls = [])
    {
        dump($methodCallsToStaticCalls);
        die;
        $this->methodCallsToStaticCalls = $methodCallsToStaticCalls;
    }

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
                    '$methodCallsToStaticCalls' => [
                        'AnotherDependency' => [['StaticCaller', 'anotherMethod']],
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

//        foreach ($this->methodCallsToStaticCalls as ) {
//    }

        // change the node

        return $node;
    }
}
