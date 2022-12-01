<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\MethodCall;

use RectorPrefix202212\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\StringType;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\PropertyNaming;
use Rector\PostRector\Collector\PropertyToAddCollector;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Rector\Symfony\TypeAnalyzer\ControllerAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Rector\MethodCall\GetParameterToConstructorInjectionRector\GetParameterToConstructorInjectionRectorTest
 */
final class GetParameterToConstructorInjectionRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\PropertyToAddCollector
     */
    private $propertyToAddCollector;
    /**
     * @readonly
     * @var \Rector\Symfony\TypeAnalyzer\ControllerAnalyzer
     */
    private $controllerAnalyzer;
    public function __construct(PropertyNaming $propertyNaming, PropertyToAddCollector $propertyToAddCollector, ControllerAnalyzer $controllerAnalyzer)
    {
        $this->propertyNaming = $propertyNaming;
        $this->propertyToAddCollector = $propertyToAddCollector;
        $this->controllerAnalyzer = $controllerAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns fetching of parameters via `getParameter()` in ContainerAware to constructor injection in Command and Controller in Symfony', [new CodeSample(<<<'CODE_SAMPLE'
class MyCommand extends ContainerAwareCommand
{
    public function someMethod()
    {
        $this->getParameter('someParameter');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class MyCommand extends Command
{
    private $someParameter;

    public function __construct($someParameter)
    {
        $this->someParameter = $someParameter;
    }

    public function someMethod()
    {
        $this->someParameter;
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
        if (!$this->isName($node->name, 'getParameter')) {
            return null;
        }
        $firstArg = $node->args[0];
        if (!$firstArg instanceof Arg) {
            return null;
        }
        $stringArgument = $firstArg->value;
        if (!$stringArgument instanceof String_) {
            return null;
        }
        $parameterName = $stringArgument->value;
        $parameterName = Strings::replace($parameterName, '#\\.#', '_');
        $propertyName = $this->propertyNaming->underscoreToName($parameterName);
        $class = $this->betterNodeFinder->findParentType($node, Class_::class);
        if (!$class instanceof Class_) {
            return null;
        }
        $propertyMetadata = new PropertyMetadata($propertyName, new StringType(), Class_::MODIFIER_PRIVATE);
        $this->propertyToAddCollector->addPropertyToClass($class, $propertyMetadata);
        return $this->nodeFactory->createPropertyFetch('this', $propertyName);
    }
}
