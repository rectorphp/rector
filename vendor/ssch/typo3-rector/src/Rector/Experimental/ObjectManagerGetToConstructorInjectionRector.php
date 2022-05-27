<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\Experimental;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\PropertyNaming;
use Rector\PostRector\Collector\PropertyToAddCollector;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/10.4/Deprecation-90803-DeprecationOfObjectManagergetInExtbaseContext.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\Experimental\ObjectManagerGetToConstructorInjectionRector\ObjectManagerGetToConstructorInjectionRectorTest
 */
final class ObjectManagerGetToConstructorInjectionRector extends AbstractRector
{
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
    public function __construct(PropertyToAddCollector $propertyToAddCollector, PropertyNaming $propertyNaming)
    {
        $this->propertyToAddCollector = $propertyToAddCollector;
        $this->propertyNaming = $propertyNaming;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns fetching of dependencies via `$objectManager->get()` to constructor injection', [new CodeSample(<<<'CODE_SAMPLE'
final class MyController extends ActionController
{
    public function someAction()
    {
        $someService = $this->objectManager->get(SomeService::class);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class MyController extends ActionController
{
    private SomeService $someService;

    public function __construct(SomeService $someService)
    {
        $this->someService = $someService;
    }

    public function someAction()
    {
        $someService = $this->someService;
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
        if (!$this->isObjectType($node->var, new ObjectType('TYPO3\\CMS\\Extbase\\Object\\ObjectManagerInterface'))) {
            return null;
        }
        if (!$this->isName($node->name, 'get')) {
            return null;
        }
        return $this->replaceMethodCallWithPropertyFetchAndDependency($node);
    }
    public function replaceMethodCallWithPropertyFetchAndDependency(MethodCall $methodCall) : ?PropertyFetch
    {
        $class = $this->valueResolver->getValue($methodCall->args[0]->value);
        if (null === $class) {
            return null;
        }
        $serviceType = new ObjectType($class);
        if ($serviceType->isInstanceOf('TYPO3\\CMS\\Extbase\\DomainObject\\DomainObjectInterface')->yes()) {
            return null;
        }
        $class = $this->betterNodeFinder->findParentType($methodCall, Class_::class);
        if (!$class instanceof Class_) {
            return null;
        }
        $propertyName = $this->propertyNaming->fqnToVariableName($serviceType);
        $propertyMetadata = new PropertyMetadata($propertyName, $serviceType, Class_::MODIFIER_PRIVATE);
        $this->propertyToAddCollector->addPropertyToClass($class, $propertyMetadata);
        return $this->nodeFactory->createPropertyFetch('this', $propertyName);
    }
}
