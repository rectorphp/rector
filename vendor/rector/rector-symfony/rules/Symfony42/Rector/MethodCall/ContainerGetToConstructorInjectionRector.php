<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony42\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\NodeManipulator\ClassDependencyManipulator;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Rector\Rector\AbstractRector;
use Rector\Symfony\DependencyInjection\NodeDecorator\CommandConstructorDecorator;
use Rector\Symfony\NodeAnalyzer\DependencyInjectionMethodCallAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Ref: https://github.com/symfony/symfony/blob/master/UPGRADE-4.0.md#console
 *
 * @deprecated This rule is deprecated as too vague and causing too many changes. Use more granular @see \Rector\Symfony\Set\SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION instead
 */
final class ContainerGetToConstructorInjectionRector extends AbstractRector implements DeprecatedInterface
{
    /**
     * @readonly
     */
    private DependencyInjectionMethodCallAnalyzer $dependencyInjectionMethodCallAnalyzer;
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private ClassDependencyManipulator $classDependencyManipulator;
    /**
     * @readonly
     */
    private CommandConstructorDecorator $commandConstructorDecorator;
    public function __construct(DependencyInjectionMethodCallAnalyzer $dependencyInjectionMethodCallAnalyzer, TestsNodeAnalyzer $testsNodeAnalyzer, ClassDependencyManipulator $classDependencyManipulator, CommandConstructorDecorator $commandConstructorDecorator)
    {
        $this->dependencyInjectionMethodCallAnalyzer = $dependencyInjectionMethodCallAnalyzer;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->classDependencyManipulator = $classDependencyManipulator;
        $this->commandConstructorDecorator = $commandConstructorDecorator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns fetching of dependencies via `$container->get()` in ContainerAware to constructor injection in Command and Controller in Symfony', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeCommand extends ContainerAwareCommand
{
    public function someMethod()
    {
        // ...
        $this->getContainer()->get('some_service');
        $this->container->get('some_service');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeCommand extends ContainerAwareCommand
{
    public function __construct(SomeService $someService)
    {
        $this->someService = $someService;
    }

    public function someMethod()
    {
        // ...
        $this->someService;
        $this->someService;
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
        if ($this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $class = $node;
        $propertyMetadatas = [];
        $this->traverseNodesWithCallable($class, function (Node $node) use($class, &$propertyMetadatas) : ?Node {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if (!$this->isName($node->name, 'get')) {
                return null;
            }
            if (!$this->isObjectType($node->var, new ObjectType('Symfony\\Component\\DependencyInjection\\ContainerInterface'))) {
                return null;
            }
            if ($node->isFirstClassCallable()) {
                return null;
            }
            $args = $node->getArgs();
            if (\count($args) === 1 && $args[0]->value instanceof ClassConstFetch) {
                return null;
            }
            $propertyMetadata = $this->dependencyInjectionMethodCallAnalyzer->replaceMethodCallWithPropertyFetchAndDependency($class, $node);
            if (!$propertyMetadata instanceof PropertyMetadata) {
                return null;
            }
            $propertyMetadatas[] = $propertyMetadata;
            return $this->nodeFactory->createPropertyFetch('this', $propertyMetadata->getName());
        });
        if ($propertyMetadatas === []) {
            return null;
        }
        foreach ($propertyMetadatas as $propertyMetadata) {
            $this->classDependencyManipulator->addConstructorDependency($class, $propertyMetadata);
        }
        $this->commandConstructorDecorator->decorate($class);
        return $node;
    }
}
