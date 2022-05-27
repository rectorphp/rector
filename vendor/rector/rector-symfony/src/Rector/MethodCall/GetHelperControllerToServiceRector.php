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
final class GetHelperControllerToServiceRector extends \Rector\Core\Rector\AbstractRector
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
    public function __construct(\Rector\Symfony\TypeAnalyzer\ControllerAnalyzer $controllerAnalyzer, \Rector\PostRector\Collector\PropertyToAddCollector $propertyToAddCollector, \Rector\Naming\Naming\PropertyNaming $propertyNaming)
    {
        $this->controllerAnalyzer = $controllerAnalyzer;
        $this->propertyToAddCollector = $propertyToAddCollector;
        $this->propertyNaming = $propertyNaming;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace $this->getDoctrine() and $this->dispatchMessage() calls in AbstractController with direct service use', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->controllerAnalyzer->isController($node->var)) {
            return null;
        }
        $class = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\Class_::class);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
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
    private function refactorDispatchMessage(\PhpParser\Node\Stmt\Class_ $class, \PhpParser\Node\Expr\MethodCall $methodCall)
    {
        $propertyName = $this->propertyNaming->fqnToVariableName('Symfony\\Component\\Messenger\\MessageBusInterface');
        // add dependency
        $propertyObjectType = new \PHPStan\Type\ObjectType('Symfony\\Component\\Messenger\\MessageBusInterface');
        $propertyMetadata = new \Rector\PostRector\ValueObject\PropertyMetadata($propertyName, $propertyObjectType, \PhpParser\Node\Stmt\Class_::MODIFIER_PRIVATE);
        $this->propertyToAddCollector->addPropertyToClass($class, $propertyMetadata);
        $thisVariable = new \PhpParser\Node\Expr\Variable('this');
        $methodCall->var = new \PhpParser\Node\Expr\PropertyFetch($thisVariable, $propertyName);
        $methodCall->name = new \PhpParser\Node\Identifier('dispatch');
        return $methodCall;
    }
    private function refactorGetDoctrine(\PhpParser\Node\Stmt\Class_ $class) : \PhpParser\Node\Expr\PropertyFetch
    {
        $propertyName = $this->propertyNaming->fqnToVariableName('Doctrine\\Persistence\\ManagerRegistry');
        // add dependency
        $propertyObjectType = new \PHPStan\Type\ObjectType('Doctrine\\Persistence\\ManagerRegistry');
        $propertyMetadata = new \Rector\PostRector\ValueObject\PropertyMetadata($propertyName, $propertyObjectType, \PhpParser\Node\Stmt\Class_::MODIFIER_PRIVATE);
        $this->propertyToAddCollector->addPropertyToClass($class, $propertyMetadata);
        $thisVariable = new \PhpParser\Node\Expr\Variable('this');
        return new \PhpParser\Node\Expr\PropertyFetch($thisVariable, $propertyName);
    }
}
