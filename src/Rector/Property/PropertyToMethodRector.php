<?php declare(strict_types=1);

namespace Rector\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use Rector\Node\MethodCallNodeFactory;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class PropertyToMethodRector extends AbstractRector
{
    /**
     * @var string[][][]
     */
    private $perClassPropertyToMethods = [];

    /**
     * @var PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var MethodCallNodeFactory
     */
    private $methodCallNodeFactory;

    /**
     * @param string[][][] $perClassPropertyToMethods
     */
    public function __construct(
        array $perClassPropertyToMethods,
        PropertyFetchAnalyzer $propertyFetchAnalyzer,
        NodeFactory $nodeFactory,
        MethodCallNodeFactory $methodCallNodeFactory
    ) {
        $this->perClassPropertyToMethods = $perClassPropertyToMethods;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->nodeFactory = $nodeFactory;
        $this->methodCallNodeFactory = $methodCallNodeFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces properties assign calls be defined methods.', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
$result = $object->property;
$object->property = $value;
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$result = $object->getProperty();
$object->setProperty($value);
CODE_SAMPLE
                ,
                [
                    '$perClassPropertyToMethods' => [
                        'SomeObject' => [
                            'property' => [
                                'get' => 'getProperty',
                                'set' => 'setProperty',
                            ],
                        ],
                    ],
                ]
            ),
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
$result = $object->property;
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$result = $object->getProperty('someArg');
CODE_SAMPLE
                ,
                [
                    '$perClassPropertyToMethods' => [
                        'SomeObject' => [
                            'property' => [
                                'get' => [
                                    'method' => 'getConfig',
                                    'arguments' => ['someArg'],
                                ],
                            ],
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
        if ($assignNode->var instanceof PropertyFetch) {
            return $this->processSetter($assignNode);
        }

        if ($assignNode->expr instanceof PropertyFetch) {
            return $this->processGetter($assignNode);
        }

        return null;
    }

    private function processSetter(Assign $assignNode): ?Node
    {
        /** @var PropertyFetch $propertyFetchNode */
        $propertyFetchNode = $assignNode->var;

        $newMethodMatch = $this->matchPropertyFetchCandidate($propertyFetchNode);
        if ($newMethodMatch === null) {
            return null;
        }

        $args = $this->nodeFactory->createArgs([$assignNode->expr]);

        /** @var Variable $variable */
        $variable = $propertyFetchNode->var;

        return $this->methodCallNodeFactory->createWithVariableMethodNameAndArguments(
            $variable,
            $newMethodMatch['set'],
            $args
        );
    }

    private function processGetter(Assign $assignNode): ?Node
    {
        /** @var PropertyFetch $propertyFetchNode */
        $propertyFetchNode = $assignNode->expr;

        $newMethodMatch = $this->matchPropertyFetchCandidate($propertyFetchNode);
        if ($newMethodMatch === null) {
            return null;
        }

        // simple method name
        if (is_string($newMethodMatch['get'])) {
            $assignNode->expr = $this->methodCallNodeFactory->createWithVariableAndMethodName(
                $propertyFetchNode->var,
                $newMethodMatch['get']
            );

            return $assignNode;

            // method with argument
        }

        if (is_array($newMethodMatch['get'])) {
            $args = $this->nodeFactory->createArgs($newMethodMatch['get']['arguments']);

            $assignNode->expr = $this->methodCallNodeFactory->createWithVariableMethodNameAndArguments(
                $propertyFetchNode->var,
                $newMethodMatch['get']['method'],
                $args
            );

            return $assignNode;
        }

        return $assignNode;
    }

    /**
     * @return mixed[]|null
     */
    private function matchPropertyFetchCandidate(PropertyFetch $propertyFetchNode): ?array
    {
        foreach ($this->perClassPropertyToMethods as $class => $propertyToMethods) {
            $properties = array_keys($propertyToMethods);

            if ($this->propertyFetchAnalyzer->isTypeAndProperties($propertyFetchNode, $class, $properties)) {
                /** @var Identifier $identifierNode */
                $identifierNode = $propertyFetchNode->name;

                return $propertyToMethods[$identifierNode->toString()]; //[$type];
            }
        }

        return null;
    }
}
