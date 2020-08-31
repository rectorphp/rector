<?php

declare(strict_types=1);

namespace Rector\Transform\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Transform\ValueObject\PropertyAssignToMethodCall;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Transform\Tests\Rector\Assign\PropertyAssignToMethodCallRector\PropertyAssignToMethodCallRectorTest
 */
final class PropertyAssignToMethodCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const PROPERTY_ASSIGNS_TO_METHODS_CALLS = 'property_assigns_to_methods_calls';

    /**
     * @var PropertyAssignToMethodCall[]
     */
    private $propertyAssignsToMethodCalls = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns property assign of specific type and property name to method call', [
            new ConfiguredCodeSample(
<<<'PHP'
$someObject = new SomeClass;
$someObject->oldProperty = false;
PHP
                ,
<<<'PHP'
$someObject = new SomeClass;
$someObject->newMethodCall(false);
PHP
                ,
                [
                    self::PROPERTY_ASSIGNS_TO_METHODS_CALLS => [
                        new PropertyAssignToMethodCall('SomeClass', 'oldProperty', 'newMethodCall'),
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
        return [Assign::class];
    }

    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->var instanceof PropertyFetch) {
            return null;
        }

        $propertyFetchNode = $node->var;

        /** @var Variable $propertyNode */
        $propertyNode = $propertyFetchNode->var;

        foreach ($this->propertyAssignsToMethodCalls as $propertyAssignToMethodCall) {
            if (! $this->isObjectType($propertyFetchNode->var, $propertyAssignToMethodCall->getClass())) {
                continue;
            }

            if (! $this->isName($propertyFetchNode, $propertyAssignToMethodCall->getOldPropertyName())) {
                continue;
            }

            return $this->createMethodCall(
                $propertyNode,
                $propertyAssignToMethodCall->getNewMethodName(),
                [$node->expr]
            );
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $propertyAssignsToMethodCalls = $configuration[self::PROPERTY_ASSIGNS_TO_METHODS_CALLS] ?? [];
        Assert::allIsInstanceOf($propertyAssignsToMethodCalls, PropertyAssignToMethodCall::class);
        $this->propertyAssignsToMethodCalls = $propertyAssignsToMethodCalls;
    }
}
