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
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Laravel\Tests\Rector\Assign\CallOnAppArrayAccessToStandaloneAssignRector\CallOnAppArrayAccessToStandaloneAssignRectorTest
 */
final class CallOnAppArrayAccessToStandaloneAssignRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var ServiceNameTypeAndVariableName[]
     */
    private $serviceNameTypeAndVariableNames = [];
    /**
     * @var \Rector\Laravel\NodeFactory\AppAssignFactory
     */
    private $appAssignFactory;
    public function __construct(\Rector\Laravel\NodeFactory\AppAssignFactory $appAssignFactory)
    {
        $this->appAssignFactory = $appAssignFactory;
        $this->serviceNameTypeAndVariableNames[] = new \Rector\Laravel\ValueObject\ServiceNameTypeAndVariableName('validator', 'Illuminate\\Validation\\Factory', 'validationFactory');
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\Assign::class];
    }
    /**
     * @param Assign $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$node->expr instanceof \PhpParser\Node\Expr\MethodCall) {
            return null;
        }
        $methodCall = $node->expr;
        if (!$methodCall->var instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            return null;
        }
        $arrayDimFetch = $methodCall->var;
        if (!$this->isObjectType($arrayDimFetch->var, new \PHPStan\Type\ObjectType('Illuminate\\Contracts\\Foundation\\Application'))) {
            return null;
        }
        $arrayDimFetchDim = $methodCall->var->dim;
        if (!$arrayDimFetchDim instanceof \PhpParser\Node\Expr) {
            return null;
        }
        foreach ($this->serviceNameTypeAndVariableNames as $serviceNameTypeAndVariableName) {
            if (!$this->valueResolver->isValue($arrayDimFetchDim, $serviceNameTypeAndVariableName->getServiceName())) {
                continue;
            }
            $assignExpression = $this->appAssignFactory->createAssignExpression($serviceNameTypeAndVariableName, $methodCall->var);
            $this->nodesToAddCollector->addNodeBeforeNode($assignExpression, $node);
            $methodCall->var = new \PhpParser\Node\Expr\Variable($serviceNameTypeAndVariableName->getVariableName());
            return $node;
        }
        return null;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace magical call on $this->app["something"] to standalone type assign variable', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
