<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony28\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use Rector\NodeManipulator\ClassDependencyManipulator;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Rector\Rector\AbstractRector;
use Rector\Symfony\NodeAnalyzer\DependencyInjectionMethodCallAnalyzer;
use Rector\Symfony\TypeAnalyzer\ContainerAwareAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Symfony28\Rector\MethodCall\GetToConstructorInjectionRector\GetToConstructorInjectionRectorTest
 */
final class GetToConstructorInjectionRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\DependencyInjectionMethodCallAnalyzer
     */
    private $dependencyInjectionMethodCallAnalyzer;
    /**
     * @readonly
     * @var \Rector\Symfony\TypeAnalyzer\ContainerAwareAnalyzer
     */
    private $containerAwareAnalyzer;
    /**
     * @readonly
     * @var \Rector\NodeManipulator\ClassDependencyManipulator
     */
    private $classDependencyManipulator;
    public function __construct(DependencyInjectionMethodCallAnalyzer $dependencyInjectionMethodCallAnalyzer, ContainerAwareAnalyzer $containerAwareAnalyzer, ClassDependencyManipulator $classDependencyManipulator)
    {
        $this->dependencyInjectionMethodCallAnalyzer = $dependencyInjectionMethodCallAnalyzer;
        $this->containerAwareAnalyzer = $containerAwareAnalyzer;
        $this->classDependencyManipulator = $classDependencyManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns fetching of dependencies via `$this->get()` to constructor injection in Command and Controller', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

final class SomeController extend Controller
{
    public function someMethod()
    {
        // ...
        $this->get('some_service');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

final class SomeController extend Controller
{
    public function __construct(SomeService $someService)
    {
        $this->someService = $someService;
    }

    public function someMethod()
    {
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
        $class = $node;
        $propertyMetadatas = [];
        $this->traverseNodesWithCallable($class, function (Node $node) use($class, &$propertyMetadatas) : ?Node {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if (!$this->isName($node->name, 'get')) {
                return null;
            }
            if (!$this->containerAwareAnalyzer->isGetMethodAwareType($node->var)) {
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
            $this->classDependencyManipulator->addConstructorDependency($node, $propertyMetadata);
        }
        return $node;
    }
}
