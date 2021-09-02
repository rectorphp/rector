<?php

declare (strict_types=1);
namespace Rector\Defluent\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Rector\AbstractRector;
use Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer;
use Rector\Defluent\NodeAnalyzer\NewFluentChainMethodCallNodeAnalyzer;
use Rector\Defluent\NodeFactory\NonFluentChainMethodCallFactory;
use Rector\Defluent\NodeFactory\VariableFromNewFactory;
use Rector\Defluent\Skipper\FluentMethodCallSkipper;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Defluent\Rector\MethodCall\MethodCallOnSetterMethodCallToStandaloneAssignRector\MethodCallOnSetterMethodCallToStandaloneAssignRectorTest
 */
final class MethodCallOnSetterMethodCallToStandaloneAssignRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Defluent\NodeAnalyzer\NewFluentChainMethodCallNodeAnalyzer
     */
    private $newFluentChainMethodCallNodeAnalyzer;
    /**
     * @var \Rector\Defluent\NodeFactory\VariableFromNewFactory
     */
    private $variableFromNewFactory;
    /**
     * @var \Rector\Defluent\NodeFactory\NonFluentChainMethodCallFactory
     */
    private $nonFluentChainMethodCallFactory;
    /**
     * @var \Rector\Defluent\Skipper\FluentMethodCallSkipper
     */
    private $fluentMethodCallSkipper;
    /**
     * @var \Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer
     */
    private $fluentChainMethodCallNodeAnalyzer;
    public function __construct(\Rector\Defluent\NodeAnalyzer\NewFluentChainMethodCallNodeAnalyzer $newFluentChainMethodCallNodeAnalyzer, \Rector\Defluent\NodeFactory\VariableFromNewFactory $variableFromNewFactory, \Rector\Defluent\NodeFactory\NonFluentChainMethodCallFactory $nonFluentChainMethodCallFactory, \Rector\Defluent\Skipper\FluentMethodCallSkipper $fluentMethodCallSkipper, \Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer $fluentChainMethodCallNodeAnalyzer)
    {
        $this->newFluentChainMethodCallNodeAnalyzer = $newFluentChainMethodCallNodeAnalyzer;
        $this->variableFromNewFactory = $variableFromNewFactory;
        $this->nonFluentChainMethodCallFactory = $nonFluentChainMethodCallFactory;
        $this->fluentMethodCallSkipper = $fluentMethodCallSkipper;
        $this->fluentChainMethodCallNodeAnalyzer = $fluentChainMethodCallNodeAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change method call on setter to standalone assign before the setter', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function some()
    {
        $this->anotherMethod(new AnotherClass())
            ->someFunction();
    }

    public function anotherMethod(AnotherClass $anotherClass)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function some()
    {
        $anotherClass = new AnotherClass();
        $anotherClass->someFunction();
        $this->anotherMethod($anotherClass);
    }

    public function anotherMethod(AnotherClass $anotherClass)
    {
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
        if ($this->fluentMethodCallSkipper->shouldSkipRootMethodCall($node)) {
            return null;
        }
        $rootMethodCall = $this->fluentChainMethodCallNodeAnalyzer->resolveRootMethodCall($node);
        if (!$rootMethodCall instanceof \PhpParser\Node\Expr\MethodCall) {
            return null;
        }
        $new = $this->newFluentChainMethodCallNodeAnalyzer->matchNewInFluentSetterMethodCall($rootMethodCall);
        if (!$new instanceof \PhpParser\Node\Expr\New_) {
            return null;
        }
        $newStmts = $this->nonFluentChainMethodCallFactory->createFromNewAndRootMethodCall($new, $node);
        $this->nodesToAddCollector->addNodesBeforeNode($newStmts, $node);
        // change new arg to root variable
        $newVariable = $this->variableFromNewFactory->create($new);
        $rootMethodCall->args = [new \PhpParser\Node\Arg($newVariable)];
        return $rootMethodCall;
    }
}
