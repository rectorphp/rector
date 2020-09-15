<?php

declare(strict_types=1);

namespace Rector\Transform\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Transform\ValueObject\PropertyToMethod;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Transform\Tests\Rector\Assign\PropertyToMethodRector\PropertyToMethodRectorTest
 */
final class PropertyToMethodRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const PROPERTIES_TO_METHOD_CALLS = 'properties_to_method_calls';

    /**
     * @var PropertyToMethod[]
     */
    private $propertiesToMethodCalls = [];

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
                    self::PROPERTIES_TO_METHOD_CALLS => [
                        new PropertyToMethod('SomeObject', 'property', 'getProperty', 'setProperty'),
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
                    self::PROPERTIES_TO_METHOD_CALLS => [
                        new PropertyToMethod('SomeObject', 'property', 'getConfig', null, ['someArg']),
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

    public function configure(array $configuration): void
    {
        $propertiesToMethodCalls = $configuration[self::PROPERTIES_TO_METHOD_CALLS] ?? [];
        Assert::allIsInstanceOf($propertiesToMethodCalls, PropertyToMethod::class);
        $this->propertiesToMethodCalls = $propertiesToMethodCalls;
    }

    private function processSetter(Assign $assign): ?Node
    {
        /** @var PropertyFetch $propertyFetchNode */
        $propertyFetchNode = $assign->var;

        $propertyToMethodCall = $this->matchPropertyFetchCandidate($propertyFetchNode);
        if ($propertyToMethodCall === null) {
            return null;
        }

        if ($propertyToMethodCall->getNewSetMethod() === null) {
            throw new ShouldNotHappenException();
        }

        $args = $this->createArgs([$assign->expr]);

        /** @var Variable $variable */
        $variable = $propertyFetchNode->var;

        return $this->createMethodCall($variable, $propertyToMethodCall->getNewSetMethod(), $args);
    }

    private function processGetter(Assign $assign): ?Node
    {
        /** @var PropertyFetch $propertyFetchNode */
        $propertyFetchNode = $assign->expr;

        $propertyToMethodCall = $this->matchPropertyFetchCandidate($propertyFetchNode);
        if ($propertyToMethodCall === null) {
            return null;
        }

        // simple method name
        if ($propertyToMethodCall->getNewGetMethod() !== '') {
            $assign->expr = $this->createMethodCall($propertyFetchNode->var, $propertyToMethodCall->getNewGetMethod());

            if ($propertyToMethodCall->getNewGetArguments() !== []) {
                $args = $this->createArgs($propertyToMethodCall->getNewGetArguments());
                $assign->expr->args = $args;
            }

            return $assign;
        }

        return $assign;
    }

    private function matchPropertyFetchCandidate(PropertyFetch $propertyFetch): ?PropertyToMethod
    {
        foreach ($this->propertiesToMethodCalls as $propertyToMethodCall) {
            if (! $this->isObjectType($propertyFetch->var, $propertyToMethodCall->getOldType())) {
                continue;
            }

            if (! $this->isName($propertyFetch, $propertyToMethodCall->getOldProperty())) {
                continue;
            }

            return $propertyToMethodCall;
        }

        return null;
    }
}
