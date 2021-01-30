<?php

declare(strict_types=1);

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
 * @see \Rector\Transform\Tests\Rector\MethodCall\VariableMethodCallToServiceCallRector\VariableMethodCallToServiceCallRectorTest
 */
final class VariableMethodCallToServiceCallRector extends AbstractRector implements ConfigurableRectorInterface
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

    public function __construct(PropertyNaming $propertyNaming)
    {
        $this->propertyNaming = $propertyNaming;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace variable method call to a service one', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
use PhpParser\Node;

class SomeClass
{
    public function run(Node $node)
    {
        $phpDocInfo = $node->getAttribute('php_doc_info');
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
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
                ,
                [
                    self::VARIABLE_METHOD_CALLS_TO_SERVICE_CALLS => [
                        new VariableMethodCallToServiceCall(
                            'PhpParser\Node',
                            'getAttribute',
                            'php_doc_info',
                            'Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory',
                            'createFromNodeOrEmpty'
                        ),
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->variableMethodCallsToServiceCalls as $variableMethodCallsToServiceCalls) {
            if (! $node->var instanceof Variable) {
                continue;
            }

            if (! $this->isObjectType($node->var, $variableMethodCallsToServiceCalls->getVariableType())) {
                continue;
            }

            if (! $this->isName($node->name, $variableMethodCallsToServiceCalls->getMethodName())) {
                continue;
            }

            $firstArgValue = $node->args[0]->value;
            if (! $this->valueResolver->isValue(
                $firstArgValue,
                $variableMethodCallsToServiceCalls->getArgumentValue()
            )) {
                continue;
            }

            $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
            if (! $classLike instanceof Class_) {
                continue;
            }

            $serviceObjectType = new ObjectType($variableMethodCallsToServiceCalls->getServiceType());

            $this->addConstructorDependency($serviceObjectType, $classLike);
            return $this->createServiceMethodCall(
                $serviceObjectType,
                $variableMethodCallsToServiceCalls->getServiceMethodName(),
                $node
            );
        }

        return null;
    }

    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        $this->variableMethodCallsToServiceCalls = $configuration[self::VARIABLE_METHOD_CALLS_TO_SERVICE_CALLS] ?? [];
    }

    private function addConstructorDependency(ObjectType $objectType, Class_ $class): void
    {
        $propertyName = $this->propertyNaming->fqnToVariableName($objectType);
        $this->addConstructorDependencyToClass($class, $objectType, $propertyName);
    }

    private function createServiceMethodCall(ObjectType $objectType, string $methodName, MethodCall $node): MethodCall
    {
        $propertyName = $this->propertyNaming->fqnToVariableName($objectType);
        $propertyFetch = new PropertyFetch(new Variable('this'), $propertyName);
        $methodCall = new MethodCall($propertyFetch, $methodName);
        $methodCall->args[] = new Arg($node->var);

        return $methodCall;
    }
}
