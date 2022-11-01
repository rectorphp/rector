<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\NodeManipulator\PropertyManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\PostRector\Collector\PropertyToAddCollector;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Rector\Transform\ValueObject\NewToMethodCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202211\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\New_\NewToMethodCallRector\NewToMethodCallRectorTest
 */
final class NewToMethodCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var NewToMethodCall[]
     */
    private $newsToMethodCalls = [];
    /**
     * @readonly
     * @var \Rector\CodingStyle\Naming\ClassNaming
     */
    private $classNaming;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\PropertyManipulator
     */
    private $propertyManipulator;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\PropertyToAddCollector
     */
    private $propertyToAddCollector;
    public function __construct(ClassNaming $classNaming, PropertyManipulator $propertyManipulator, PropertyToAddCollector $propertyToAddCollector)
    {
        $this->classNaming = $classNaming;
        $this->propertyManipulator = $propertyManipulator;
        $this->propertyToAddCollector = $propertyToAddCollector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replaces creating object instances with "new" keyword with factory method.', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
	public function example() {
		new MyClass($argument);
	}
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
	/**
	 * @var \MyClassFactory
	 */
	private $myClassFactory;

	public function example() {
		$this->myClassFactory->create($argument);
	}
}
CODE_SAMPLE
, [new NewToMethodCall('MyClass', 'MyClassFactory', 'create')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [New_::class];
    }
    /**
     * @param New_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $class = $this->betterNodeFinder->findParentType($node, Class_::class);
        if (!$class instanceof Class_) {
            return null;
        }
        $className = $this->getName($class);
        if (!\is_string($className)) {
            return null;
        }
        foreach ($this->newsToMethodCalls as $newToMethodCall) {
            if (!$this->isObjectType($node, $newToMethodCall->getNewObjectType())) {
                continue;
            }
            $serviceObjectType = $newToMethodCall->getServiceObjectType();
            if ($className === $serviceObjectType->getClassName()) {
                continue;
            }
            $propertyName = $this->propertyManipulator->resolveExistingClassPropertyNameByType($class, $newToMethodCall->getServiceObjectType());
            if ($propertyName === null) {
                $serviceObjectType = $newToMethodCall->getServiceObjectType();
                $propertyName = $this->classNaming->getShortName($serviceObjectType->getClassName());
                $propertyName = \lcfirst($propertyName);
                $propertyMetadata = new PropertyMetadata($propertyName, $newToMethodCall->getServiceObjectType(), Class_::MODIFIER_PRIVATE);
                $this->propertyToAddCollector->addPropertyToClass($class, $propertyMetadata);
            }
            $propertyFetch = new PropertyFetch(new Variable('this'), $propertyName);
            return new MethodCall($propertyFetch, $newToMethodCall->getServiceMethod(), $node->args);
        }
        return $node;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsAOf($configuration, NewToMethodCall::class);
        $this->newsToMethodCalls = $configuration;
    }
}
