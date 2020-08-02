<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Generic\Tests\Rector\Assign\PropertyAssignToMethodCallRector\PropertyAssignToMethodCallRectorTest
 */
final class PropertyAssignToMethodCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const OLD_PROPERTIES_TO_NEW_METHOD_CALLS_BY_TYPE = '$oldPropertiesToNewMethodCallsByType';

    /**
     * @var string[][]
     */
    private $oldPropertiesToNewMethodCallsByType = [];

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
                    self::OLD_PROPERTIES_TO_NEW_METHOD_CALLS_BY_TYPE => [
                        'SomeClass' => [
                            'oldPropertyName' => 'oldProperty',
                            'newMethodName' => 'newMethodCall',
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

        foreach ($this->oldPropertiesToNewMethodCallsByType as $type => $oldPropertiesToNewMethodCalls) {
            if (! $this->isObjectType($propertyFetchNode->var, $type)) {
                continue;
            }

            foreach ($oldPropertiesToNewMethodCalls as $oldProperty => $newMethodCall) {
                if (! $this->isName($propertyFetchNode, $oldProperty)) {
                    continue;
                }

                return $this->createMethodCall($propertyNode, $newMethodCall, [$node->expr]);
            }
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $this->oldPropertiesToNewMethodCallsByType = $configuration[self::OLD_PROPERTIES_TO_NEW_METHOD_CALLS_BY_TYPE] ?? [];
    }
}
