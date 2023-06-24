<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony60\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Core\NodeManipulator\ClassDependencyManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\PropertyNaming;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Rector\Symfony\TypeAnalyzer\ControllerAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/symfony/symfony/pull/42422
 * @changelog https://github.com/symfony/symfony/pull/1195
 *
 * @see \Rector\Symfony\Tests\Symfony60\Rector\MethodCall\GetHelperControllerToServiceRector\GetHelperControllerToServiceRectorTest
 */
final class GetHelperControllerToServiceRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Symfony\TypeAnalyzer\ControllerAnalyzer
     */
    private $controllerAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ClassDependencyManipulator
     */
    private $classDependencyManipulator;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    public function __construct(ControllerAnalyzer $controllerAnalyzer, ClassDependencyManipulator $classDependencyManipulator, PropertyNaming $propertyNaming)
    {
        $this->controllerAnalyzer = $controllerAnalyzer;
        $this->classDependencyManipulator = $classDependencyManipulator;
        $this->propertyNaming = $propertyNaming;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace $this->getDoctrine() and $this->dispatchMessage() calls in AbstractController with direct service use', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class SomeController extends AbstractController
{
    public function run()
    {
        $productRepository = $this->getDoctrine()->getRepository(Product::class);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;

final class SomeController extends AbstractController
{
    public function __construct(
        private ManagerRegistry $managerRegistry
    ) {
    }

    public function run()
    {
        $productRepository = $this->managerRegistry->getRepository(Product::class);
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
        if (!$this->controllerAnalyzer->isController($node)) {
            return null;
        }
        $propertyMetadatas = [];
        $this->traverseNodesWithCallable($node, function (Node $node) use(&$propertyMetadatas) {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if (!$this->isName($node->var, 'this')) {
                return null;
            }
            if ($this->isName($node->name, 'getDoctrine')) {
                $entityManagerPropertyMetadata = $this->createManagerRegistryPropertyMetadata();
                $propertyMetadatas[] = $entityManagerPropertyMetadata;
                return $this->nodeFactory->createPropertyFetch('this', $entityManagerPropertyMetadata->getName());
            }
            if ($this->isName($node->name, 'dispatchMessage')) {
                $eventDispatcherPropertyMetadata = $this->createMessageBusPropertyMetadata();
                $propertyMetadatas[] = $eventDispatcherPropertyMetadata;
                $thisVariable = new Variable('this');
                $node->var = new PropertyFetch($thisVariable, new Identifier($eventDispatcherPropertyMetadata->getName()));
                $node->name = new Identifier('dispatch');
                return $node;
            }
            return null;
        });
        if ($propertyMetadatas === []) {
            return null;
        }
        foreach ($propertyMetadatas as $propertyMetadata) {
            $this->classDependencyManipulator->addConstructorDependency($node, $propertyMetadata);
        }
        return $node;
    }
    private function createMessageBusPropertyMetadata() : PropertyMetadata
    {
        $propertyName = $this->propertyNaming->fqnToVariableName('Symfony\\Component\\Messenger\\MessageBusInterface');
        // add dependency
        $propertyObjectType = new ObjectType('Symfony\\Component\\Messenger\\MessageBusInterface');
        return new PropertyMetadata($propertyName, $propertyObjectType);
    }
    private function createManagerRegistryPropertyMetadata() : PropertyMetadata
    {
        $propertyName = $this->propertyNaming->fqnToVariableName('Doctrine\\Persistence\\ManagerRegistry');
        // add dependency
        $propertyObjectType = new ObjectType('Doctrine\\Persistence\\ManagerRegistry');
        return new PropertyMetadata($propertyName, $propertyObjectType);
    }
}
