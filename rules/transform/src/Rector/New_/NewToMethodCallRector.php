<?php

declare(strict_types=1);

namespace Rector\Transform\Rector\New_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Transform\ValueObject\NewToMethodCall;
use ReflectionClass;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Transform\Tests\Rector\New_\NewToMethodCallRector\NewToMethodCallRectorTest
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
    private $newsToMethodCalls = [];

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
     * @return string[]
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
        foreach ($this->newsToMethodCalls as $newToMethodCall) {
            if (! $this->isObjectType($node, $newToMethodCall->getNewType())) {
                continue;
            }

            $serviceType = $newToMethodCall->getServiceType();
            $className = $node->getAttribute(AttributeKey::CLASS_NAME);
            if ($className === $serviceType) {
                continue;
            }

            /** @var Class_ $classNode */
            $classNode = $node->getAttribute(AttributeKey::CLASS_NODE);
            $propertyName = $this->getExistingFactoryPropertyName($classNode, $serviceType);

            if ($propertyName === null) {
                $propertyName = $this->getFactoryPropertyName($serviceType);

                $factoryObjectType = new ObjectType($serviceType);

                $this->addConstructorDependencyToClass($classNode, $factoryObjectType, $propertyName);
            }

            $propertyFetch = new PropertyFetch(new Variable('this'), $propertyName);

            return new MethodCall($propertyFetch, $newToMethodCall->getServiceMethod(), $node->args);
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

    private function getExistingFactoryPropertyName(Class_ $class, string $factoryClass): ?string
    {
        foreach ($class->getProperties() as $property) {
            if (! $this->isObjectType($property, $factoryClass)) {
                continue;
            }

            return $this->getName($property);
        }

        return null;
    }

    private function getFactoryPropertyName(string $factoryFullQualifiedName): string
    {
        $reflectionClass = new ReflectionClass($factoryFullQualifiedName);
        $shortName = $reflectionClass->getShortName();

        return Strings::firstLower($shortName);
    }
}
