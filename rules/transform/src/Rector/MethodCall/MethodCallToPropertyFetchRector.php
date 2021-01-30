<?php

declare(strict_types=1);

namespace Rector\Transform\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Transform\Tests\Rector\MethodCall\MethodCallToPropertyFetchRector\MethodCallToPropertyFetchRectorTest
 */
final class MethodCallToPropertyFetchRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const METHOD_CALL_TO_PROPERTY_FETCHES = 'method_call_to_property_fetch_collection';

    /**
     * @var array<string, string>
     */
    private $methodCallToPropertyFetchCollection = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Turns method call "$this->something()" to property fetch "$this->something"', [
            new ConfiguredCodeSample(
<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $this->someMethod();
    }
}
CODE_SAMPLE
                ,
<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $this->someProperty;
    }
}
CODE_SAMPLE
                ,
                [
                    self::METHOD_CALL_TO_PROPERTY_FETCHES => [
                        'someMethod' => 'someProperty',
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
        foreach ($this->methodCallToPropertyFetchCollection as $methodName => $propertyName) {
            if (! $this->isName($node->name, $methodName)) {
                continue;
            }

            return $this->nodeFactory->createPropertyFetch('this', $propertyName);
        }

        return null;
    }

    public function configure(array $configuration): void
    {
        $this->methodCallToPropertyFetchCollection = $configuration[self::METHOD_CALL_TO_PROPERTY_FETCHES] ?? [];
    }
}
