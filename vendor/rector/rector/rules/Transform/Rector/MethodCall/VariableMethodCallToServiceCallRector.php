<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Transform\ValueObject\VariableMethodCallToServiceCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Transform\Rector\MethodCall\VariableMethodCallToServiceCallRector\VariableMethodCallToServiceCallRectorTest
 */
final class VariableMethodCallToServiceCallRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const VARIABLE_METHOD_CALLS_TO_SERVICE_CALLS = 'variable_method_calls_to_service_calls';
    /**
     * @var VariableMethodCallToServiceCall[]
     */
    private $variableMethodCallsToServiceCalls = [];
    /**
     * @var PropertyNaming
     */
    private $propertyNaming;
    public function __construct(\Rector\Naming\Naming\PropertyNaming $propertyNaming)
    {
        $this->propertyNaming = $propertyNaming;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace variable method call to a service one', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
use PhpParser\Node;

class SomeClass
{
    public function run(Node $node)
    {
        $phpDocInfo = $node->getAttribute('php_doc_info');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use PhpParser\Node;

class SomeClass
{
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function run(Node $node)
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
    }
}
CODE_SAMPLE
, [self::VARIABLE_METHOD_CALLS_TO_SERVICE_CALLS => [new \Rector\Transform\ValueObject\VariableMethodCallToServiceCall('PhpParser\\Node', 'getAttribute', 'php_doc_info', 'Rector\\BetterPhpDocParser\\PhpDocInfo\\PhpDocInfoFactory', 'createFromNodeOrEmpty')]])]);
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
        foreach ($this->variableMethodCallsToServiceCalls as $variableMethodCallToServiceCall) {
            if (!$node->var instanceof \PhpParser\Node\Expr\Variable) {
                continue;
            }
            if (!$this->isObjectType($node->var, $variableMethodCallToServiceCall->getVariableObjectType())) {
                continue;
            }
            if (!$this->isName($node->name, $variableMethodCallToServiceCall->getMethodName())) {
                continue;
            }
            $firstArgValue = $node->args[0]->value;
            if (!$this->valueResolver->isValue($firstArgValue, $variableMethodCallToServiceCall->getArgumentValue())) {
                continue;
            }
            $classLike = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
            if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
                continue;
            }
            $serviceObjectType = new \PHPStan\Type\ObjectType($variableMethodCallToServiceCall->getServiceType());
            $this->addConstructorDependency($serviceObjectType, $classLike);
            return $this->createServiceMethodCall($serviceObjectType, $variableMethodCallToServiceCall->getServiceMethodName(), $node);
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $this->variableMethodCallsToServiceCalls = $configuration[self::VARIABLE_METHOD_CALLS_TO_SERVICE_CALLS] ?? [];
    }
    private function addConstructorDependency(\PHPStan\Type\ObjectType $objectType, \PhpParser\Node\Stmt\Class_ $class) : void
    {
        $propertyName = $this->propertyNaming->fqnToVariableName($objectType);
        $this->addConstructorDependencyToClass($class, $objectType, $propertyName);
    }
    private function createServiceMethodCall(\PHPStan\Type\ObjectType $objectType, string $methodName, \PhpParser\Node\Expr\MethodCall $node) : \PhpParser\Node\Expr\MethodCall
    {
        $propertyName = $this->propertyNaming->fqnToVariableName($objectType);
        $propertyFetch = new \PhpParser\Node\Expr\PropertyFetch(new \PhpParser\Node\Expr\Variable('this'), $propertyName);
        $methodCall = new \PhpParser\Node\Expr\MethodCall($propertyFetch, $methodName);
        $methodCall->args[] = new \PhpParser\Node\Arg($node->var);
        return $methodCall;
    }
}
