<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\PropertyNaming;
use Rector\PostRector\Collector\PropertyToAddCollector;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Rector\Symfony\TypeAnalyzer\ControllerAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/symfony/symfony/pull/42422
 * @changelog https://github.com/symfony/symfony/pull/1195
 *
 * @see \Rector\Symfony\Tests\Rector\MethodCall\GetHelperControllerToServiceRector\GetHelperControllerToServiceRectorTest
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
     * @var \Rector\PostRector\Collector\PropertyToAddCollector
     */
    private $propertyToAddCollector;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    public function __construct(ControllerAnalyzer $controllerAnalyzer, PropertyToAddCollector $propertyToAddCollector, PropertyNaming $propertyNaming)
    {
        $this->controllerAnalyzer = $controllerAnalyzer;
        $this->propertyToAddCollector = $propertyToAddCollector;
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
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->controllerAnalyzer->isController($node->var)) {
            return null;
        }
        $class = $this->betterNodeFinder->findParentType($node, Class_::class);
        if (!$class instanceof Class_) {
            return null;
        }
        if ($this->isName($node->name, 'getDoctrine')) {
            return $this->refactorGetDoctrine($class);
        }
        if ($this->isName($node->name, 'dispatchMessage')) {
            return $this->refactorDispatchMessage($class, $node);
        }
        return null;
    }
    /**
     * @return \PhpParser\Node|\PhpParser\Node\Expr\MethodCall
     */
    private function refactorDispatchMessage(Class_ $class, MethodCall $methodCall)
    {
        $propertyName = $this->propertyNaming->fqnToVariableName('Symfony\\Component\\Messenger\\MessageBusInterface');
        // add dependency
        $propertyObjectType = new ObjectType('Symfony\\Component\\Messenger\\MessageBusInterface');
        $propertyMetadata = new PropertyMetadata($propertyName, $propertyObjectType, Class_::MODIFIER_PRIVATE);
        $this->propertyToAddCollector->addPropertyToClass($class, $propertyMetadata);
        $thisVariable = new Variable('this');
        $methodCall->var = new PropertyFetch($thisVariable, $propertyName);
        $methodCall->name = new Identifier('dispatch');
        return $methodCall;
    }
    private function refactorGetDoctrine(Class_ $class) : PropertyFetch
    {
        $propertyName = $this->propertyNaming->fqnToVariableName('Doctrine\\Persistence\\ManagerRegistry');
        // add dependency
        $propertyObjectType = new ObjectType('Doctrine\\Persistence\\ManagerRegistry');
        $propertyMetadata = new PropertyMetadata($propertyName, $propertyObjectType, Class_::MODIFIER_PRIVATE);
        $this->propertyToAddCollector->addPropertyToClass($class, $propertyMetadata);
        $thisVariable = new Variable('this');
        return new PropertyFetch($thisVariable, $propertyName);
    }
}
