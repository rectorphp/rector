<?php declare(strict_types=1);

namespace Rector\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\Node\MethodCallNodeFactory;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class PropertyAssignToMethodCallRector extends AbstractRector
{
    /**
     * @var string[][]
     */
    private $oldPropertiesToNewMethodCallsByType = [];

    /**
     * @var MethodCallNodeFactory
     */
    private $methodCallNodeFactory;

    /**
     * @param string[][] $oldPropertiesToNewMethodCallsByType
     */
    public function __construct(
        MethodCallNodeFactory $methodCallNodeFactory,
        array $oldPropertiesToNewMethodCallsByType
    ) {
        $this->methodCallNodeFactory = $methodCallNodeFactory;
        $this->oldPropertiesToNewMethodCallsByType = $oldPropertiesToNewMethodCallsByType;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns property assign of specific type and property name to method call', [
            new ConfiguredCodeSample(
<<<'CODE_SAMPLE'
$someObject = new SomeClass; 
$someObject->oldProperty = false;
CODE_SAMPLE
                ,
<<<'CODE_SAMPLE'
$someObject = new SomeClass;
$someObject->newMethodCall(false);
CODE_SAMPLE
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
            if (! $this->isType($propertyFetchNode, $type)) {
                continue;
            }

            foreach ($oldPropertiesToNewMethodCalls as $oldProperty => $newMethodCall) {
                if (! $this->isName($propertyFetchNode, $oldProperty)) {
                    continue;
                }

                return $this->methodCallNodeFactory->createWithVariableMethodNameAndArguments(
                    $propertyNode,
                    $newMethodCall,
                    [$node->expr]
                );
            }
        }

        return $node;
    }
}
