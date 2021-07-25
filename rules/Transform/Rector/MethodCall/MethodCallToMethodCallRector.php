<?php

declare(strict_types=1);

namespace Rector\Transform\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\NodeAnalyzer\PropertyPresenceChecker;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Collector\PropertyToAddCollector;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Rector\Transform\ValueObject\MethodCallToMethodCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Tests\Transform\Rector\MethodCall\MethodCallToMethodCallRector\MethodCallToMethodCallRectorTest
 */
final class MethodCallToMethodCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const METHOD_CALLS_TO_METHOD_CALLS = 'method_calls_to_method_calls';

    /**
     * @var MethodCallToMethodCall[]
     */
    private array $methodCallsToMethodsCalls = [];

    public function __construct(
        private PropertyNaming $propertyNaming,
        private PropertyPresenceChecker $propertyPresenceChecker,
        private PropertyToAddCollector $propertyToAddCollector
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change method one method from one service to a method call to in another service', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(
        private FirstDependency $firstDependency
    ) {
    }

    public function run()
    {
        $this->firstDependency->go();
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(
        private SecondDependency $secondDependency
    ) {
    }

    public function run()
    {
        $this->secondDependency->away();
    }
}
CODE_SAMPLE
                ,
                [
                    self::METHOD_CALLS_TO_METHOD_CALLS => [
                        new MethodCallToMethodCall('FirstDependency', 'go', 'SecondDependency', 'away'),
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
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->methodCallsToMethodsCalls as $methodCallToMethodCall) {
            if (! $node->var instanceof PropertyFetch) {
                continue;
            }

            if (! $this->isMatch($node, $methodCallToMethodCall)) {
                continue;
            }

            $propertyFetch = $node->var;

            $class = $node->getAttribute(AttributeKey::CLASS_NODE);
            $newObjectType = new ObjectType($methodCallToMethodCall->getNewType());

            $newPropertyName = $this->matchNewPropertyName($methodCallToMethodCall, $class);
            if ($newPropertyName === null) {
                continue;
            }

            $propertyMetadata = new PropertyMetadata($newPropertyName, $newObjectType, Class_::MODIFIER_PRIVATE);
            $this->propertyToAddCollector->addPropertyToClass($class, $propertyMetadata);

            // rename property
            $node->var = new PropertyFetch($propertyFetch->var, $newPropertyName);
            // rename method
            $node->name = new Identifier($methodCallToMethodCall->getNewMethod());

            return $node;
        }

        return null;
    }

    /**
     * @param array<string, MethodCallToMethodCall[]> $configuration
     */
    public function configure(array $configuration): void
    {
        $methodCallsToMethodsCalls = $configuration[self::METHOD_CALLS_TO_METHOD_CALLS] ?? [];
        Assert::allIsAOf($methodCallsToMethodsCalls, MethodCallToMethodCall::class);
        $this->methodCallsToMethodsCalls = $methodCallsToMethodsCalls;
    }

    private function isMatch(MethodCall $methodCall, MethodCallToMethodCall $methodCallToMethodCall): bool
    {
        if (! $this->isObjectType($methodCall->var, new ObjectType($methodCallToMethodCall->getOldType()))) {
            return false;
        }

        return $this->isName($methodCall->name, $methodCallToMethodCall->getOldMethod());
    }

    private function matchNewPropertyName(MethodCallToMethodCall $methodCallToMethodCall, Class_ $class): ?string
    {
        $newPropertyName = $this->propertyNaming->fqnToVariableName($methodCallToMethodCall->getNewType());

        $propertyMetadata = new PropertyMetadata(
            $newPropertyName,
            new ObjectType($methodCallToMethodCall->getNewType()),
            Class_::MODIFIER_PRIVATE
        );

        $classContextProperty = $this->propertyPresenceChecker->getClassContextProperty($class, $propertyMetadata);
        if ($classContextProperty === null) {
            return $newPropertyName;
        }

        // re-use existing proeprty name
        return $this->getName($classContextProperty);
    }
}
