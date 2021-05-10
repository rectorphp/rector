<?php

declare(strict_types=1);

namespace Rector\Transform\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\NodeManipulator\MagicPropertyFetchAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Transform\ValueObject\GetAndSetToMethodCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Tests\Transform\Rector\Assign\GetAndSetToMethodCallRector\GetAndSetToMethodCallRectorTest
 */
final class GetAndSetToMethodCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const TYPE_TO_METHOD_CALLS = 'type_to_method_calls';

    /**
     * @var GetAndSetToMethodCall[]
     */
    private $getAndSetToMethodCalls = [];

    public function __construct(
        private PropertyFetchAnalyzer $propertyFetchAnalyzer,
        private MagicPropertyFetchAnalyzer $magicPropertyFetchAnalyzer
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Turns defined `__get`/`__set` to specific method calls.', [
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
                        new GetAndSetToMethodCall('SomeContainer', 'addService', 'getService'),
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
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
            if ($node->var instanceof PropertyFetch) {
                return $this->processMagicSet($node->expr, $node->var);
            }

            return null;
        }

        return $this->processPropertyFetch($node);
    }

    /**
     * @param array<string, GetAndSetToMethodCall[]> $configuration
     */
    public function configure(array $configuration): void
    {
        $getAndSetToMethodCalls = $configuration[self::TYPE_TO_METHOD_CALLS] ?? [];
        Assert::allIsAOf($getAndSetToMethodCalls, GetAndSetToMethodCall::class);

        $this->getAndSetToMethodCalls = $getAndSetToMethodCalls;
    }

    private function processMagicSet(Expr $expr, PropertyFetch $propertyFetch): ?Node
    {
        foreach ($this->getAndSetToMethodCalls as $getAndSetToMethodCall) {
            $objectType = $getAndSetToMethodCall->getObjectType();
            if ($this->shouldSkipPropertyFetch($propertyFetch, $objectType)) {
                continue;
            }

            return $this->createMethodCallNodeFromAssignNode(
                $propertyFetch,
                $expr,
                $getAndSetToMethodCall->getSetMethod()
            );
        }

        return null;
    }

    private function processPropertyFetch(PropertyFetch $propertyFetch): ?MethodCall
    {
        $parentNode = $propertyFetch->getAttribute(AttributeKey::PARENT_NODE);

        foreach ($this->getAndSetToMethodCalls as $getAndSetToMethodCall) {
            if ($this->shouldSkipPropertyFetch($propertyFetch, $getAndSetToMethodCall->getObjectType())) {
                continue;
            }

            // setter, skip
            if (! $parentNode instanceof Assign) {
                return $this->createMethodCallNodeFromPropertyFetchNode(
                    $propertyFetch,
                    $getAndSetToMethodCall->getGetMethod()
                );
            }

            if ($parentNode->var !== $propertyFetch) {
                return $this->createMethodCallNodeFromPropertyFetchNode(
                    $propertyFetch,
                    $getAndSetToMethodCall->getGetMethod()
                );
            }
        }

        return null;
    }

    private function shouldSkipPropertyFetch(PropertyFetch $propertyFetch, ObjectType $objectType): bool
    {
        if (! $this->isObjectType($propertyFetch->var, $objectType)) {
            return true;
        }

        if (! $this->magicPropertyFetchAnalyzer->isMagicOnType($propertyFetch, $objectType)) {
            return true;
        }

        return $this->propertyFetchAnalyzer->isPropertyToSelf($propertyFetch);
    }

    private function createMethodCallNodeFromAssignNode(
        PropertyFetch $propertyFetch,
        Expr $expr,
        string $method
    ): MethodCall {
        $propertyName = $this->getName($propertyFetch->name);
        return $this->nodeFactory->createMethodCall($propertyFetch->var, $method, [$propertyName, $expr]);
    }

    private function createMethodCallNodeFromPropertyFetchNode(
        PropertyFetch $propertyFetch,
        string $method
    ): MethodCall {
        /** @var Variable $variableNode */
        $variableNode = $propertyFetch->var;

        return $this->nodeFactory->createMethodCall($variableNode, $method, [$this->getName($propertyFetch)]);
    }
}
