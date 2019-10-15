<?php

declare(strict_types=1);

namespace Rector\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Tests\Rector\Assign\PropertyAssignToMethodCallRector\PropertyAssignToMethodCallRectorTest
 */
final class PropertyAssignToMethodCallRector extends AbstractRector
{
    /**
     * @var string[][]
     */
    private $oldPropertiesToNewMethodCallsByType = [];

    /**
     * @param string[][] $oldPropertiesToNewMethodCallsByType
     */
    public function __construct(array $oldPropertiesToNewMethodCallsByType = [])
    {
        $this->oldPropertiesToNewMethodCallsByType = $oldPropertiesToNewMethodCallsByType;
    }

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
                    '$oldPropertiesToNewMethodCallsByType' => [
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
}
