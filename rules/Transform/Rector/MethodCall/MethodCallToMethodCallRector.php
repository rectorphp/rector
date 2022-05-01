<?php

declare (strict_types=1);
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
use Rector\PostRector\Collector\PropertyToAddCollector;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Rector\Transform\ValueObject\MethodCallToMethodCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220501\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\MethodCall\MethodCallToMethodCallRector\MethodCallToMethodCallRectorTest
 */
final class MethodCallToMethodCallRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
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
    public function __construct(\Rector\Naming\Naming\PropertyNaming $propertyNaming, \Rector\Core\NodeAnalyzer\PropertyPresenceChecker $propertyPresenceChecker, \Rector\PostRector\Collector\PropertyToAddCollector $propertyToAddCollector)
    {
        $this->propertyNaming = $propertyNaming;
        $this->propertyPresenceChecker = $propertyPresenceChecker;
        $this->propertyToAddCollector = $propertyToAddCollector;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change method one method from one service to a method call to in another service', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
, [new \Rector\Transform\ValueObject\MethodCallToMethodCall('FirstDependency', 'go', 'SecondDependency', 'away')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        foreach ($this->methodCallsToMethodsCalls as $methodCallToMethodCall) {
            if (!$node->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
                continue;
            }
            if (!$this->isMatch($node, $methodCallToMethodCall)) {
                continue;
            }
            $propertyFetch = $node->var;
            $class = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\Class_::class);
            if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
                continue;
            }
            $newObjectType = new \PHPStan\Type\ObjectType($methodCallToMethodCall->getNewType());
            $newPropertyName = $this->matchNewPropertyName($methodCallToMethodCall, $class);
            if ($newPropertyName === null) {
                continue;
            }
            $propertyMetadata = new \Rector\PostRector\ValueObject\PropertyMetadata($newPropertyName, $newObjectType, \PhpParser\Node\Stmt\Class_::MODIFIER_PRIVATE);
            $this->propertyToAddCollector->addPropertyToClass($class, $propertyMetadata);
            // rename property
            $node->var = new \PhpParser\Node\Expr\PropertyFetch($propertyFetch->var, $newPropertyName);
            // rename method
            $node->name = new \PhpParser\Node\Identifier($methodCallToMethodCall->getNewMethod());
            return $node;
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        \RectorPrefix20220501\Webmozart\Assert\Assert::allIsAOf($configuration, \Rector\Transform\ValueObject\MethodCallToMethodCall::class);
        $this->methodCallsToMethodsCalls = $configuration;
    }
    private function isMatch(\PhpParser\Node\Expr\MethodCall $methodCall, \Rector\Transform\ValueObject\MethodCallToMethodCall $methodCallToMethodCall) : bool
    {
        if (!$this->isObjectType($methodCall->var, new \PHPStan\Type\ObjectType($methodCallToMethodCall->getOldType()))) {
            return \false;
        }
        return $this->isName($methodCall->name, $methodCallToMethodCall->getOldMethod());
    }
    private function matchNewPropertyName(\Rector\Transform\ValueObject\MethodCallToMethodCall $methodCallToMethodCall, \PhpParser\Node\Stmt\Class_ $class) : ?string
    {
        $newPropertyName = $this->propertyNaming->fqnToVariableName($methodCallToMethodCall->getNewType());
        $propertyMetadata = new \Rector\PostRector\ValueObject\PropertyMetadata($newPropertyName, new \PHPStan\Type\ObjectType($methodCallToMethodCall->getNewType()), \PhpParser\Node\Stmt\Class_::MODIFIER_PRIVATE);
        $classContextProperty = $this->propertyPresenceChecker->getClassContextProperty($class, $propertyMetadata);
        if ($classContextProperty === null) {
            return $newPropertyName;
        }
        // re-use existing property name
        return $this->getName($classContextProperty);
    }
}
