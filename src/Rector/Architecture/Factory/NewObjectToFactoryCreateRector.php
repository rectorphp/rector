<?php declare(strict_types=1);

namespace Rector\Rector\Architecture\Factory;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;
use ReflectionClass;

final class NewObjectToFactoryCreateRector extends AbstractRector
{
    /**
     * @var string[][]
     */
    private $objectToFactoryMethod = [];

    /**
     * @param string[][] $objectToFactoryMethod
     */
    public function __construct(array $objectToFactoryMethod)
    {
        $this->objectToFactoryMethod = $objectToFactoryMethod;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces creating object instances with "new" keyword with factory method.', [
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
                ['MyClass' => [
                    'class' => 'MyClassFactory',
                    'method' => 'create',
                ]]
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
            if (! $this->isType($node, $object)) {
                continue;
            }

            /** @var Class_ $classNode */
            $classNode = $node->getAttribute(Attribute::CLASS_NODE);
            $factoryClass = $factoryInfo['class'];
            $factoryMethod = $factoryInfo['method'];
            $propertyName = $this->getExistingFactoryPropertyName($classNode, $factoryClass);

            if ($propertyName === null) {
                $propertyName = $this->getFactoryPropertyName($factoryClass);

                $this->addPropertyToClass($classNode, $factoryClass, $propertyName);
            }

            return new MethodCall(
                new PropertyFetch(new Variable('this'), $propertyName),
                $factoryMethod,
                $node->args
            );
        }

        return $node;
    }

    private function getExistingFactoryPropertyName(Class_ $classNode, string $factoryClass): ?string
    {
        foreach ($classNode->stmts as $node) {
            if ($node instanceof Property && $this->isType($node, $factoryClass)) {
                return (string) $node->props[0]->name;
            }
        }

        return null;
    }

    private function getFactoryPropertyName(string $factoryFullQualifiedName): string
    {
        $className = (new ReflectionClass($factoryFullQualifiedName))->getShortName();

        return Strings::firstLower($className);
    }
}
