<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\PhpParser\Node\Manipulator\PropertyFetchManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\MagicDisclosure\Tests\Rector\Assign\GetAndSetToMethodCallRector\GetAndSetToMethodCallRectorTest
 */
final class GetAndSetToMethodCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const TYPE_TO_METHOD_CALLS = 'type_to_method_calls';

    /**
     * @var string[][]
     */
    private $typeToMethodCalls = [];

    /**
     * @var PropertyFetchManipulator
     */
    private $propertyFetchManipulator;

    public function __construct(PropertyFetchManipulator $propertyFetchManipulator)
    {
        $this->propertyFetchManipulator = $propertyFetchManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns defined `__get`/`__set` to specific method calls.', [
            new ConfiguredCodeSample(
<<<'CODE_SAMPLE'
$container = new SomeContainer;
$container->someService = $someService;
CODE_SAMPLE
                ,
<<<'CODE_SAMPLE'
$container = new SomeContainer;
$container->setService("someService", $someService);
CODE_SAMPLE
                ,
                [
                    self::TYPE_TO_METHOD_CALLS => [
                        'SomeContainer' => [
                            'set' => 'addService',
                        ],
                    ],
                ]
            ),
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
$container = new SomeContainer;
$someService = $container->someService;
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$container = new SomeContainer;
$someService = $container->getService("someService");
CODE_SAMPLE
                ,
                [
                    self::TYPE_TO_METHOD_CALLS => [
                        'SomeContainer' => [
                            'get' => 'getService',
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
        return [Assign::class, PropertyFetch::class];
    }

    /**
     * @param Assign|PropertyFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Assign) {
            if ($node->var instanceof PropertyFetch || $node->var instanceof StaticPropertyFetch) {
                return $this->processMagicSet($node);
            }

            return null;
        }

        return $this->processPropertyFetch($node);
    }

    public function configure(array $configuration): void
    {
        $this->typeToMethodCalls = $configuration[self::TYPE_TO_METHOD_CALLS] ?? [];
    }

    private function processMagicSet(Assign $assign): ?Node
    {
        /** @var PropertyFetch $propertyFetchNode */
        $propertyFetchNode = $assign->var;

        foreach ($this->typeToMethodCalls as $type => $transformation) {
            $objectType = new ObjectType($type);
            if ($this->shouldSkipPropertyFetch($propertyFetchNode, $objectType)) {
                continue;
            }

            return $this->createMethodCallNodeFromAssignNode(
                $propertyFetchNode,
                $assign->expr,
                $transformation['set']
            );
        }

        return null;
    }

    private function processPropertyFetch(PropertyFetch $propertyFetch): ?MethodCall
    {
        foreach ($this->typeToMethodCalls as $type => $transformation) {
            $objectType = new ObjectType($type);
            if ($this->shouldSkipPropertyFetch($propertyFetch, $objectType)) {
                continue;
            }

            // setter, skip
            $parentNode = $propertyFetch->getAttribute(AttributeKey::PARENT_NODE);

            if ($parentNode instanceof Assign && $parentNode->var === $propertyFetch) {
                continue;
            }

            return $this->createMethodCallNodeFromPropertyFetchNode($propertyFetch, $transformation['get']);
        }

        return null;
    }

    private function shouldSkipPropertyFetch(PropertyFetch $propertyFetch, ObjectType $objectType): bool
    {
        if (! $this->isObjectType($propertyFetch->var, $objectType)) {
            return true;
        }

        if (! $this->propertyFetchManipulator->isMagicOnType($propertyFetch, $objectType)) {
            return true;
        }

        // $this->value = $value
        return $this->propertyFetchManipulator->isPropertyToSelf($propertyFetch);
    }

    private function createMethodCallNodeFromAssignNode(
        PropertyFetch $propertyFetch,
        Node $node,
        string $method
    ): MethodCall {
        /** @var Variable $variableNode */
        $variableNode = $propertyFetch->var;

        return $this->createMethodCall($variableNode, $method, [$this->getName($propertyFetch), $node]);
    }

    private function createMethodCallNodeFromPropertyFetchNode(
        PropertyFetch $propertyFetch,
        string $method
    ): MethodCall {
        /** @var Variable $variableNode */
        $variableNode = $propertyFetch->var;

        return $this->createMethodCall($variableNode, $method, [$this->getName($propertyFetch)]);
    }
}
