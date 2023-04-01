<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\Class_;

use RectorPrefix202304\Nette\Utils\Strings;
use PhpParser\Builder\Property;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Symfony\NodeAnalyzer\ServiceTypeMethodCallResolver;
use Rector\Symfony\NodeFactory\RequiredClassMethodFactory;
use Rector\Symfony\TypeAnalyzer\ContainerAwareAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Rector\Class_\ContainerGetToRequiredDependencyAbstractClassRector\ContainerGetToRequiredDependencyAbstractClassRectorTest
 */
final class ContainerGetToRequiredDependencyAbstractClassRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $autowiredTypes = [];
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\ServiceTypeMethodCallResolver
     */
    private $serviceTypeMethodCallResolver;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeFactory\RequiredClassMethodFactory
     */
    private $requiredClassMethodFactory;
    /**
     * @readonly
     * @var \Rector\Symfony\TypeAnalyzer\ContainerAwareAnalyzer
     */
    private $containerAwareAnalyzer;
    public function __construct(ServiceTypeMethodCallResolver $serviceTypeMethodCallResolver, RequiredClassMethodFactory $requiredClassMethodFactory, ContainerAwareAnalyzer $containerAwareAnalyzer)
    {
        $this->serviceTypeMethodCallResolver = $serviceTypeMethodCallResolver;
        $this->requiredClassMethodFactory = $requiredClassMethodFactory;
        $this->containerAwareAnalyzer = $containerAwareAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change $this->get("some_service"); to @required dependency in an abstract class', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class CustomAbstractController extends AbstractController
{
    public function run()
    {
        $this->get('some_service')->apply();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class CustomAbstractController extends AbstractController
{
    private SomeService $someService;

    /**
     * @required
     */
    public function autowire(SomeService $someService)
    {
        $this->someService = $someService;
    }

    public function run()
    {
        $this->someService->apply();
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
        if (!$node->isAbstract()) {
            return null;
        }
        if (!$this->containerAwareAnalyzer->isGetMethodAwareType($node)) {
            return null;
        }
        $this->autowiredTypes = [];
        // collect all $this->get("some_service") calls
        $this->traverseNodesWithCallable($node->stmts, function (Node $node) {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if (!$this->isThisGetMethodCall($node)) {
                return null;
            }
            $serviceType = $this->serviceTypeMethodCallResolver->resolve($node);
            if (!$serviceType instanceof ObjectType) {
                return null;
            }
            $this->autowiredTypes[] = $serviceType->getClassName();
            $variableName = $this->resolveVariableNameFromClassName($serviceType->getClassName());
            return new PropertyFetch(new Variable('this'), $variableName);
        });
        if ($this->autowiredTypes === []) {
            return null;
        }
        $autowiredTypes = \array_unique($this->autowiredTypes);
        $newStmts = $this->createPropertyStmts($autowiredTypes);
        $newStmts[] = $this->requiredClassMethodFactory->createRequiredAutowireClassMethod($this->autowiredTypes, $node);
        $node->stmts = \array_merge($newStmts, $node->stmts);
        return $node;
    }
    private function isThisGetMethodCall(MethodCall $methodCall) : bool
    {
        if (!$methodCall->var instanceof Variable) {
            return \false;
        }
        if (!$this->isName($methodCall->var, 'this')) {
            return \false;
        }
        if (!$this->isName($methodCall->name, 'get')) {
            return \false;
        }
        return \count($methodCall->getArgs()) === 1;
    }
    private function resolveVariableNameFromClassName(string $className) : string
    {
        $shortClassName = Strings::after($className, '\\', -1);
        if (!\is_string($shortClassName)) {
            $shortClassName = $className;
        }
        return \lcfirst($shortClassName);
    }
    /**
     * @param string[] $autowiredTypes
     * @return Node\Stmt\Property[]
     */
    private function createPropertyStmts(array $autowiredTypes) : array
    {
        $properties = [];
        foreach ($autowiredTypes as $autowiredType) {
            $propertyName = $this->resolveVariableNameFromClassName($autowiredType);
            $propertyBuilder = new Property($propertyName);
            $propertyBuilder->makePrivate();
            $propertyBuilder->setType(new FullyQualified($autowiredType));
            $properties[] = $propertyBuilder->getNode();
        }
        return $properties;
    }
}
