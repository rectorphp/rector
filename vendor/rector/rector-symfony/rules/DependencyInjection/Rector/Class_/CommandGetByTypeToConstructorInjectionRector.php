<?php

declare (strict_types=1);
namespace Rector\Symfony\DependencyInjection\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ClassReflection;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeManipulator\ClassDependencyManipulator;
use Rector\PHPStan\ScopeFetcher;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\Symfony\DependencyInjection\NodeDecorator\CommandConstructorDecorator;
use Rector\Symfony\DependencyInjection\ThisGetTypeMatcher;
use Rector\Symfony\Enum\SymfonyClass;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\DependencyInjection\Rector\Class_\CommandGetByTypeToConstructorInjectionRector\CommandGetByTypeToConstructorInjectionRectorTest
 */
final class CommandGetByTypeToConstructorInjectionRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ClassDependencyManipulator $classDependencyManipulator;
    /**
     * @readonly
     */
    private PropertyNaming $propertyNaming;
    /**
     * @readonly
     */
    private CommandConstructorDecorator $commandConstructorDecorator;
    /**
     * @readonly
     */
    private ThisGetTypeMatcher $thisGetTypeMatcher;
    public function __construct(ClassDependencyManipulator $classDependencyManipulator, PropertyNaming $propertyNaming, CommandConstructorDecorator $commandConstructorDecorator, ThisGetTypeMatcher $thisGetTypeMatcher)
    {
        $this->classDependencyManipulator = $classDependencyManipulator;
        $this->propertyNaming = $propertyNaming;
        $this->commandConstructorDecorator = $commandConstructorDecorator;
        $this->thisGetTypeMatcher = $thisGetTypeMatcher;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('From `$container->get(SomeType::class)` in commands to constructor injection (step 2/x)', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

final class SomeCommand extends ContainerAwareCommand
{
    public function someMethod()
    {
        $someType = $this->get(SomeType::class);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

final class SomeCommand extends ContainerAwareCommand
{
    public function __construct(private SomeType $someType)
    {
    }

    public function someMethod()
    {
        $someType = $this->someType;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkipClass($node)) {
            return null;
        }
        $propertyMetadatas = [];
        $this->traverseNodesWithCallable($node, function (Node $node) use(&$propertyMetadatas) : ?Node {
            if (!$node instanceof MethodCall) {
                return null;
            }
            $className = $this->thisGetTypeMatcher->match($node);
            if (!\is_string($className)) {
                return null;
            }
            $propertyName = $this->propertyNaming->fqnToVariableName($className);
            $propertyMetadata = new PropertyMetadata($propertyName, new FullyQualifiedObjectType($className));
            $propertyMetadatas[] = $propertyMetadata;
            return $this->nodeFactory->createPropertyFetch('this', $propertyMetadata->getName());
        });
        if ($propertyMetadatas === []) {
            return null;
        }
        foreach ($propertyMetadatas as $propertyMetadata) {
            $this->classDependencyManipulator->addConstructorDependency($node, $propertyMetadata);
        }
        $this->commandConstructorDecorator->decorate($node);
        return $node;
    }
    private function shouldSkipClass(Class_ $class) : bool
    {
        // keep it safe
        if ($class->isAbstract()) {
            return \true;
        }
        $scope = ScopeFetcher::fetch($class);
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return \true;
        }
        return !$classReflection->is(SymfonyClass::CONTAINER_AWARE_COMMAND);
    }
}
