<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Collector\PropertyToAddCollector;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Rector\Transform\ValueObject\NewToMethodCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20211020\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\New_\NewToMethodCallRector\NewToMethodCallRectorTest
 */
final class NewToMethodCallRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const NEWS_TO_METHOD_CALLS = 'news_to_method_calls';
    /**
     * @var NewToMethodCall[]
     */
    private $newsToMethodCalls = [];
    /**
     * @var \Rector\CodingStyle\Naming\ClassNaming
     */
    private $classNaming;
    /**
     * @var \Rector\PostRector\Collector\PropertyToAddCollector
     */
    private $propertyToAddCollector;
    public function __construct(\Rector\CodingStyle\Naming\ClassNaming $classNaming, \Rector\PostRector\Collector\PropertyToAddCollector $propertyToAddCollector)
    {
        $this->classNaming = $classNaming;
        $this->propertyToAddCollector = $propertyToAddCollector;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replaces creating object instances with "new" keyword with factory method.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
, [self::NEWS_TO_METHOD_CALLS => [new \Rector\Transform\ValueObject\NewToMethodCall('MyClass', 'MyClassFactory', 'create')]])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\New_::class];
    }
    /**
     * @param New_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        foreach ($this->newsToMethodCalls as $newsToMethodCall) {
            if (!$this->isObjectType($node, $newsToMethodCall->getNewObjectType())) {
                continue;
            }
            $serviceObjectType = $newsToMethodCall->getServiceObjectType();
            $className = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
            if ($className === $serviceObjectType->getClassName()) {
                continue;
            }
            /** @var Class_ $class */
            $class = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
            $propertyName = $this->getExistingFactoryPropertyName($class, $newsToMethodCall->getServiceObjectType());
            if ($propertyName === null) {
                $serviceObjectType = $newsToMethodCall->getServiceObjectType();
                $propertyName = $this->classNaming->getShortName($serviceObjectType->getClassName());
                $propertyName = \lcfirst($propertyName);
                $propertyMetadata = new \Rector\PostRector\ValueObject\PropertyMetadata($propertyName, $newsToMethodCall->getServiceObjectType(), \PhpParser\Node\Stmt\Class_::MODIFIER_PRIVATE);
                $this->propertyToAddCollector->addPropertyToClass($class, $propertyMetadata);
            }
            $propertyFetch = new \PhpParser\Node\Expr\PropertyFetch(new \PhpParser\Node\Expr\Variable('this'), $propertyName);
            return new \PhpParser\Node\Expr\MethodCall($propertyFetch, $newsToMethodCall->getServiceMethod(), $node->args);
        }
        return $node;
    }
    /**
     * @param array<string, NewToMethodCall[]> $configuration
     */
    public function configure(array $configuration) : void
    {
        $newsToMethodCalls = $configuration[self::NEWS_TO_METHOD_CALLS] ?? [];
        \RectorPrefix20211020\Webmozart\Assert\Assert::allIsInstanceOf($newsToMethodCalls, \Rector\Transform\ValueObject\NewToMethodCall::class);
        $this->newsToMethodCalls = $newsToMethodCalls;
    }
    private function getExistingFactoryPropertyName(\PhpParser\Node\Stmt\Class_ $class, \PHPStan\Type\ObjectType $factoryObjectType) : ?string
    {
        foreach ($class->getProperties() as $property) {
            if (!$this->isObjectType($property, $factoryObjectType)) {
                continue;
            }
            return $this->getName($property);
        }
        return null;
    }
}
