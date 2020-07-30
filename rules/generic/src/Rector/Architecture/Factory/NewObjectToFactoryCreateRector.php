<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\Architecture\Factory;

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
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionClass;

/**
 * @see \Rector\Generic\Tests\Rector\Architecture\Factory\NewObjectToFactoryCreateRector\NewObjectToFactoryCreateRectorTest
 */
final class NewObjectToFactoryCreateRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const OBJECT_TO_FACTORY_METHOD = '$objectToFactoryMethod';

    /**
     * @var string[][]
     */
    private $objectToFactoryMethod = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces creating object instances with "new" keyword with factory method.', [
            new ConfiguredCodeSample(
                <<<'PHP'
class SomeClass
{
	public function example() {
		new MyClass($argument);
	}
}
PHP
                ,
                <<<'PHP'
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
PHP
                ,
                [
                    self::OBJECT_TO_FACTORY_METHOD => [
                        'MyClass' => [
                            'class' => 'MyClassFactory',
                            'method' => 'create',
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
        return [New_::class];
    }

    /**
     * @param New_ $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->objectToFactoryMethod as $object => $factoryInfo) {
            if (! $this->isObjectType($node, $object)) {
                continue;
            }

            $factoryClass = $factoryInfo['class'];
            $factoryMethod = $factoryInfo['method'];

            if ($node->getAttribute(AttributeKey::CLASS_NAME) === $factoryClass) {
                continue;
            }

            /** @var Class_ $classNode */
            $classNode = $node->getAttribute(AttributeKey::CLASS_NODE);
            $propertyName = $this->getExistingFactoryPropertyName($classNode, $factoryClass);

            if ($propertyName === null) {
                $propertyName = $this->getFactoryPropertyName($factoryClass);

                $factoryObjectType = new ObjectType($factoryClass);

                $this->addPropertyToClass($classNode, $factoryObjectType, $propertyName);
            }

            return new MethodCall(
                new PropertyFetch(new Variable('this'), $propertyName),
                $factoryMethod,
                $node->args
            );
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $this->objectToFactoryMethod = $configuration[self::OBJECT_TO_FACTORY_METHOD] ?? [];
    }

    private function getExistingFactoryPropertyName(Class_ $class, string $factoryClass): ?string
    {
        foreach ($class->getProperties() as $property) {
            if ($this->isObjectType($property, $factoryClass)) {
                return (string) $property->props[0]->name;
            }
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
