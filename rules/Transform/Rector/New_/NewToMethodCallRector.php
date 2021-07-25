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
use Rector\NodeTypeResolver\Node\AttributeKey;
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
     * @var string
     */
    public const NEWS_TO_METHOD_CALLS = 'news_to_method_calls';

    /**
     * @var NewToMethodCall[]
     */
    private array $newsToMethodCalls = [];

    public function __construct(
        private ClassNaming $classNaming,
        private PropertyToAddCollector $propertyToAddCollector
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
                [
                    self::NEWS_TO_METHOD_CALLS => [new NewToMethodCall('MyClass', 'MyClassFactory', 'create')],
                ]
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
        foreach ($this->newsToMethodCalls as $newsToMethodCall) {
            if (! $this->isObjectType($node, $newsToMethodCall->getNewObjectType())) {
                continue;
            }

            $serviceObjectType = $newsToMethodCall->getServiceObjectType();
            $className = $node->getAttribute(AttributeKey::CLASS_NAME);
            if ($className === $serviceObjectType->getClassName()) {
                continue;
            }

            /** @var Class_ $class */
            $class = $node->getAttribute(AttributeKey::CLASS_NODE);

            $propertyName = $this->getExistingFactoryPropertyName(
                $class,
                $newsToMethodCall->getServiceObjectType()
            );

            if ($propertyName === null) {
                $serviceObjectType = $newsToMethodCall->getServiceObjectType();
                $propertyName = $this->classNaming->getShortName($serviceObjectType->getClassName());
                $propertyName = lcfirst($propertyName);

                $propertyMetadata = new PropertyMetadata(
                    $propertyName,
                    $newsToMethodCall->getServiceObjectType(),
                    Class_::MODIFIER_PRIVATE
                );
                $this->propertyToAddCollector->addPropertyToClass($class, $propertyMetadata);
            }

            $propertyFetch = new PropertyFetch(new Variable('this'), $propertyName);

            return new MethodCall($propertyFetch, $newsToMethodCall->getServiceMethod(), $node->args);
        }

        return $node;
    }

    /**
     * @param array<string, NewToMethodCall[]> $configuration
     */
    public function configure(array $configuration): void
    {
        $newsToMethodCalls = $configuration[self::NEWS_TO_METHOD_CALLS] ?? [];
        Assert::allIsInstanceOf($newsToMethodCalls, NewToMethodCall::class);
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
