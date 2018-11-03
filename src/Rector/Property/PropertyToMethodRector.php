<?php declare(strict_types=1);

namespace Rector\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
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
     * @param string[][][] $perClassPropertyToMethods
     */
    public function __construct(array $perClassPropertyToMethods)
    {
        $this->perClassPropertyToMethods = $perClassPropertyToMethods;
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
     * @param Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->var instanceof PropertyFetch) {
            return $this->processSetter($node);
        }

        if ($node->expr instanceof PropertyFetch) {
            return $this->processGetter($node);
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

        $args = $this->createArgs([$assignNode->expr]);

        /** @var Variable $variable */
        $variable = $propertyFetchNode->var;

        return $this->createMethodCall($variable, $newMethodMatch['set'], $args);
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
            $assignNode->expr = $this->createMethodCall($propertyFetchNode->var, $newMethodMatch['get']);

            return $assignNode;

            // method with argument
        }

        if (is_array($newMethodMatch['get'])) {
            $args = $this->createArgs($newMethodMatch['get']['arguments']);

            $assignNode->expr = $this->createMethodCall(
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
        foreach ($this->perClassPropertyToMethods as $type => $propertyToMethods) {
            $properties = array_keys($propertyToMethods);

            if (! $this->isType($propertyFetchNode, $type)) {
                continue;
            }

            if (! $this->isNames($propertyFetchNode, $properties)) {
                continue;
            }

            /** @var Identifier $identifierNode */
            $identifierNode = $propertyFetchNode->name;

            return $propertyToMethods[$identifierNode->toString()]; //[$type];
        }

        return null;
    }
}
