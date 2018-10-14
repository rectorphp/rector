<?php declare(strict_types=1);

namespace Rector\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\Node\MethodCallNodeFactory;
use Rector\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class PropertyAssignToMethodCallRector extends AbstractRector
{
    /**
     * @var PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;

    /**
     * @var MethodCallNodeFactory
     */
    private $methodCallNodeFactory;

    /**
     * @var string[][]
     */
    private $oldPropertiesToNewMethodCallsByType = [];

    /**
     * @param string[][] $oldPropertiesToNewMethodCallsByType
     */
    public function __construct(
        PropertyFetchAnalyzer $propertyFetchAnalyzer,
        MethodCallNodeFactory $methodCallNodeFactory,
        array $oldPropertiesToNewMethodCallsByType
    ) {
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
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
     * @param Assign $assignNode
     */
    public function refactor(Node $assignNode): ?Node
    {
        foreach ($this->oldPropertiesToNewMethodCallsByType as $type => $oldPropertiesToNewMethodCalls) {
            if (! $this->propertyFetchAnalyzer->isTypeAndProperties(
                $assignNode->var,
                $type,
                array_keys($oldPropertiesToNewMethodCalls)
            )) {
                continue;
            }

            /** @var PropertyFetch $propertyFetchNode */
            $propertyFetchNode = $assignNode->var;

            return $this->processPropertyFetch($propertyFetchNode, $oldPropertiesToNewMethodCalls, $assignNode->expr);
        }

        return $assignNode;
    }

    /**
     * @param string[] $oldPropertiesToNewMethodCalls
     */
    private function processPropertyFetch(
        PropertyFetch $propertyFetchNode,
        array $oldPropertiesToNewMethodCalls,
        Node $assignedNode
    ): MethodCall {
        /** @var Variable $propertyNode */
        $propertyNode = $propertyFetchNode->var;

        foreach ($oldPropertiesToNewMethodCalls as $oldProperty => $newMethodCall) {
            if ((string) $propertyFetchNode->name === $oldProperty) {
                return $this->methodCallNodeFactory->createWithVariableMethodNameAndArguments(
                    $propertyNode,
                    $newMethodCall,
                    [$assignedNode]
                );
            }
        }
    }
}
