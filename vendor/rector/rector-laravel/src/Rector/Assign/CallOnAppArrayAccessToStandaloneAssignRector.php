<?php

declare (strict_types=1);
namespace Rector\Laravel\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Laravel\NodeFactory\AppAssignFactory;
use Rector\Laravel\ValueObject\ServiceNameTypeAndVariableName;
use Rector\PostRector\Collector\NodesToAddCollector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Laravel\Tests\Rector\Assign\CallOnAppArrayAccessToStandaloneAssignRector\CallOnAppArrayAccessToStandaloneAssignRectorTest
 */
final class CallOnAppArrayAccessToStandaloneAssignRector extends AbstractRector
{
    /**
     * @var ServiceNameTypeAndVariableName[]
     */
    private $serviceNameTypeAndVariableNames = [];
    /**
     * @readonly
     * @var \Rector\Laravel\NodeFactory\AppAssignFactory
     */
    private $appAssignFactory;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\NodesToAddCollector
     */
    private $nodesToAddCollector;
    public function __construct(AppAssignFactory $appAssignFactory, NodesToAddCollector $nodesToAddCollector)
    {
        $this->appAssignFactory = $appAssignFactory;
        $this->nodesToAddCollector = $nodesToAddCollector;
        $this->serviceNameTypeAndVariableNames[] = new ServiceNameTypeAndVariableName('validator', 'Illuminate\\Validation\\Factory', 'validationFactory');
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Assign::class];
    }
    /**
     * @param Assign $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$node->expr instanceof MethodCall) {
            return null;
        }
        $methodCall = $node->expr;
        if (!$methodCall->var instanceof ArrayDimFetch) {
            return null;
        }
        $arrayDimFetch = $methodCall->var;
        if (!$this->isObjectType($arrayDimFetch->var, new ObjectType('Illuminate\\Contracts\\Foundation\\Application'))) {
            return null;
        }
        $arrayDimFetchDim = $methodCall->var->dim;
        if (!$arrayDimFetchDim instanceof Expr) {
            return null;
        }
        foreach ($this->serviceNameTypeAndVariableNames as $serviceNameTypeAndVariableName) {
            if (!$this->valueResolver->isValue($arrayDimFetchDim, $serviceNameTypeAndVariableName->getServiceName())) {
                continue;
            }
            $assignExpression = $this->appAssignFactory->createAssignExpression($serviceNameTypeAndVariableName, $methodCall->var);
            $this->nodesToAddCollector->addNodeBeforeNode($assignExpression, $node);
            $methodCall->var = new Variable($serviceNameTypeAndVariableName->getVariableName());
            return $node;
        }
        return null;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace magical call on $this->app["something"] to standalone type assign variable', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var \Illuminate\Contracts\Foundation\Application
     */
    private $app;

    public function run()
    {
        $validator = $this->app['validator']->make('...');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var \Illuminate\Contracts\Foundation\Application
     */
    private $app;

    public function run()
    {
        /** @var \Illuminate\Validation\Factory $validationFactory */
        $validationFactory = $this->app['validator'];
        $validator = $validationFactory->make('...');
    }
}
CODE_SAMPLE
)]);
    }
}
