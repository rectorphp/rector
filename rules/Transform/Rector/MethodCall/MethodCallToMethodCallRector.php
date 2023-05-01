<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\NodeAnalyzer\PropertyPresenceChecker;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\PropertyNaming;
use Rector\PostRector\Collector\PropertyToAddCollector;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Rector\Transform\ValueObject\MethodCallToMethodCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202305\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\MethodCall\MethodCallToMethodCallRector\MethodCallToMethodCallRectorTest
 */
final class MethodCallToMethodCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var MethodCallToMethodCall[]
     */
    private $methodCallsToMethodsCalls = [];
    /**
     * @readonly
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\PropertyPresenceChecker
     */
    private $propertyPresenceChecker;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\PropertyToAddCollector
     */
    private $propertyToAddCollector;
    public function __construct(PropertyNaming $propertyNaming, PropertyPresenceChecker $propertyPresenceChecker, PropertyToAddCollector $propertyToAddCollector)
    {
        $this->propertyNaming = $propertyNaming;
        $this->propertyPresenceChecker = $propertyPresenceChecker;
        $this->propertyToAddCollector = $propertyToAddCollector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change method one method from one service to a method call to in another service', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
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
, [new MethodCallToMethodCall('FirstDependency', 'go', 'SecondDependency', 'away')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        $class = $this->betterNodeFinder->findParentType($node, Class_::class);
        if (!$class instanceof Class_) {
            return null;
        }
        $isMethodCallCurrentClass = $this->isMethodCallCurrentClass($node);
        if (!$node->var instanceof PropertyFetch && !$isMethodCallCurrentClass) {
            return null;
        }
        foreach ($this->methodCallsToMethodsCalls as $methodCallToMethodCall) {
            if (!$this->isMatch($node, $methodCallToMethodCall, $isMethodCallCurrentClass, $class)) {
                continue;
            }
            $newPropertyName = $this->matchNewPropertyName($methodCallToMethodCall, $class);
            if ($newPropertyName === null) {
                continue;
            }
            /** @var PropertyFetch $propertyFetch */
            $propertyFetch = $isMethodCallCurrentClass ? $node : $node->var;
            $newObjectType = new ObjectType($methodCallToMethodCall->getNewType());
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
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsAOf($configuration, MethodCallToMethodCall::class);
        $this->methodCallsToMethodsCalls = $configuration;
    }
    private function isMethodCallCurrentClass(MethodCall $methodCall) : bool
    {
        return $methodCall->var instanceof Variable && $methodCall->var->name === 'this';
    }
    private function isMatch(MethodCall $methodCall, MethodCallToMethodCall $methodCallToMethodCall, bool $isMethodCallCurrentClass, Class_ $class) : bool
    {
        $oldTypeObject = new ObjectType($methodCallToMethodCall->getOldType());
        if ($isMethodCallCurrentClass) {
            $className = (string) $this->nodeNameResolver->getName($class);
            $objectType = new ObjectType($className);
            if (!$objectType->equals($oldTypeObject)) {
                return \false;
            }
            return $this->isName($methodCall->name, $methodCallToMethodCall->getOldMethod());
        }
        if (!$this->isName($methodCall->name, $methodCallToMethodCall->getOldMethod())) {
            return \false;
        }
        return $this->isObjectType($methodCall->var, $oldTypeObject);
    }
    private function matchNewPropertyName(MethodCallToMethodCall $methodCallToMethodCall, Class_ $class) : ?string
    {
        $newPropertyName = $this->propertyNaming->fqnToVariableName($methodCallToMethodCall->getNewType());
        $propertyMetadata = new PropertyMetadata($newPropertyName, new ObjectType($methodCallToMethodCall->getNewType()), Class_::MODIFIER_PRIVATE);
        $classContextProperty = $this->propertyPresenceChecker->getClassContextProperty($class, $propertyMetadata);
        if ($classContextProperty === null) {
            return $newPropertyName;
        }
        // re-use existing property name
        return $this->getName($classContextProperty);
    }
}
