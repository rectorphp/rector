<?php

declare(strict_types=1);

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
use Rector\PostRector\Collector\PropertyToAddCollector;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Rector\Transform\ValueObject\NewToMethodCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Tests\Transform\Rector\New_\NewToMethodCallRector\NewToMethodCallRectorTest
 */
final class NewToMethodCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @deprecated
     * @var string
     */
    final public const NEWS_TO_METHOD_CALLS = 'news_to_method_calls';

    /**
     * @var NewToMethodCall[]
     */
    private array $newsToMethodCalls = [];

    public function __construct(
        private readonly ClassNaming $classNaming,
        private readonly PropertyToAddCollector $propertyToAddCollector
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replaces creating object instances with "new" keyword with factory method.', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
	public function example() {
		new MyClass($argument);
	}
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
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
                ,
                [new NewToMethodCall('MyClass', 'MyClassFactory', 'create')]
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [New_::class];
    }

    /**
     * @param New_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $class = $this->betterNodeFinder->findParentType($node, Class_::class);
        if (! $class instanceof Class_) {
            return null;
        }

        $className = $this->getName($class);
        if (! is_string($className)) {
            return null;
        }

        foreach ($this->newsToMethodCalls as $newToMethodCall) {
            if (! $this->isObjectType($node, $newToMethodCall->getNewObjectType())) {
                continue;
            }

            $serviceObjectType = $newToMethodCall->getServiceObjectType();
            if ($className === $serviceObjectType->getClassName()) {
                continue;
            }

            $propertyName = $this->getExistingFactoryPropertyName(
                $class,
                $newToMethodCall->getServiceObjectType()
            );

            if ($propertyName === null) {
                $serviceObjectType = $newToMethodCall->getServiceObjectType();
                $propertyName = $this->classNaming->getShortName($serviceObjectType->getClassName());
                $propertyName = lcfirst($propertyName);

                $propertyMetadata = new PropertyMetadata(
                    $propertyName,
                    $newToMethodCall->getServiceObjectType(),
                    Class_::MODIFIER_PRIVATE
                );
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
    public function configure(array $configuration): void
    {
        $newsToMethodCalls = $configuration[self::NEWS_TO_METHOD_CALLS] ?? $configuration;
        Assert::isArray($newsToMethodCalls);
        Assert::allIsAOf($newsToMethodCalls, NewToMethodCall::class);

        $this->newsToMethodCalls = $newsToMethodCalls;
    }

    private function getExistingFactoryPropertyName(Class_ $class, ObjectType $factoryObjectType): ?string
    {
        foreach ($class->getProperties() as $property) {
            if (! $this->isObjectType($property, $factoryObjectType)) {
                continue;
            }

            return $this->getName($property);
        }

        return null;
    }
}
