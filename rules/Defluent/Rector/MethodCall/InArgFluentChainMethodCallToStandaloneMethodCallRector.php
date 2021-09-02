<?php

declare (strict_types=1);
namespace Rector\Defluent\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\Defluent\Matcher\AssignAndRootExprAndNodesToAddMatcher;
use Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer;
use Rector\Defluent\NodeAnalyzer\NewFluentChainMethodCallNodeAnalyzer;
use Rector\Defluent\NodeFactory\FluentMethodCallAsArgFactory;
use Rector\Defluent\NodeFactory\NonFluentChainMethodCallFactory;
use Rector\Defluent\NodeFactory\VariableFromNewFactory;
use Rector\Defluent\ValueObject\AssignAndRootExprAndNodesToAdd;
use Rector\Defluent\ValueObject\FluentCallsKind;
use Rector\NodeNestingScope\ParentFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Defluent\Rector\MethodCall\InArgFluentChainMethodCallToStandaloneMethodCallRector\InArgFluentChainMethodCallToStandaloneMethodCallRectorTest
 */
final class InArgFluentChainMethodCallToStandaloneMethodCallRector extends \Rector\Core\Rector\AbstractRector
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
     * @var \Rector\Defluent\NodeFactory\FluentMethodCallAsArgFactory
     */
    private $fluentMethodCallAsArgFactory;
    /**
     * @var \Rector\Defluent\Matcher\AssignAndRootExprAndNodesToAddMatcher
     */
    private $assignAndRootExprAndNodesToAddMatcher;
    /**
     * @var \Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer
     */
    private $fluentChainMethodCallNodeAnalyzer;
    /**
     * @var \Rector\Defluent\NodeFactory\NonFluentChainMethodCallFactory
     */
    private $nonFluentChainMethodCallFactory;
    /**
     * @var \Rector\NodeNestingScope\ParentFinder
     */
    private $parentFinder;
    public function __construct(\Rector\Defluent\NodeAnalyzer\NewFluentChainMethodCallNodeAnalyzer $newFluentChainMethodCallNodeAnalyzer, \Rector\Defluent\NodeFactory\VariableFromNewFactory $variableFromNewFactory, \Rector\Defluent\NodeFactory\FluentMethodCallAsArgFactory $fluentMethodCallAsArgFactory, \Rector\Defluent\Matcher\AssignAndRootExprAndNodesToAddMatcher $assignAndRootExprAndNodesToAddMatcher, \Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer $fluentChainMethodCallNodeAnalyzer, \Rector\Defluent\NodeFactory\NonFluentChainMethodCallFactory $nonFluentChainMethodCallFactory, \Rector\NodeNestingScope\ParentFinder $parentFinder)
    {
        $this->newFluentChainMethodCallNodeAnalyzer = $newFluentChainMethodCallNodeAnalyzer;
        $this->variableFromNewFactory = $variableFromNewFactory;
        $this->fluentMethodCallAsArgFactory = $fluentMethodCallAsArgFactory;
        $this->assignAndRootExprAndNodesToAddMatcher = $assignAndRootExprAndNodesToAddMatcher;
        $this->fluentChainMethodCallNodeAnalyzer = $fluentChainMethodCallNodeAnalyzer;
        $this->nonFluentChainMethodCallFactory = $nonFluentChainMethodCallFactory;
        $this->parentFinder = $parentFinder;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns fluent interface calls to classic ones.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class UsedAsParameter
{
    public function someFunction(FluentClass $someClass)
    {
        $this->processFluentClass($someClass->someFunction()->otherFunction());
    }

    public function processFluentClass(FluentClass $someClass)
    {
    }
}

CODE_SAMPLE
, <<<'CODE_SAMPLE'
class UsedAsParameter
{
    public function someFunction(FluentClass $someClass)
    {
        $someClass->someFunction();
        $someClass->otherFunction();
        $this->processFluentClass($someClass);
    }

    public function processFluentClass(FluentClass $someClass)
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
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parent instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $parentMethodCall = $parent->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentMethodCall instanceof \PhpParser\Node\Expr\MethodCall) {
            return null;
        }
        if (!$this->fluentChainMethodCallNodeAnalyzer->isLastChainMethodCall($node)) {
            return null;
        }
        $isInReturnOrCast = (bool) $this->parentFinder->findByTypes($node, [\PhpParser\Node\Stmt\Return_::class, \PhpParser\Node\Expr\Cast::class]);
        if ($isInReturnOrCast) {
            return null;
        }
        // create instances from (new ...)->call, re-use from
        if ($node->var instanceof \PhpParser\Node\Expr\New_) {
            $this->refactorNew($node, $node->var);
            return null;
        }
        $assignAndRootExprAndNodesToAdd = $this->assignAndRootExprAndNodesToAddMatcher->match($node, \Rector\Defluent\ValueObject\FluentCallsKind::IN_ARGS);
        if (!$assignAndRootExprAndNodesToAdd instanceof \Rector\Defluent\ValueObject\AssignAndRootExprAndNodesToAdd) {
            return null;
        }
        $this->nodesToAddCollector->addNodesBeforeNode($assignAndRootExprAndNodesToAdd->getNodesToAdd(), $node);
        return $assignAndRootExprAndNodesToAdd->getRootCallerExpr();
    }
    private function refactorNew(\PhpParser\Node\Expr\MethodCall $methodCall, \PhpParser\Node\Expr\New_ $new) : void
    {
        if (!$this->newFluentChainMethodCallNodeAnalyzer->isNewMethodCallReturningSelf($methodCall)) {
            return;
        }
        $nodesToAdd = $this->nonFluentChainMethodCallFactory->createFromNewAndRootMethodCall($new, $methodCall);
        $newVariable = $this->variableFromNewFactory->create($new);
        $nodesToAdd[] = $this->fluentMethodCallAsArgFactory->createFluentAsArg($methodCall, $newVariable);
        $this->nodesToAddCollector->addNodesBeforeNode($nodesToAdd, $methodCall);
        $this->removeParentParent($methodCall);
    }
    private function removeParentParent(\PhpParser\Node\Expr\MethodCall $methodCall) : void
    {
        /** @var Arg $parent */
        $parent = $methodCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        /** @var MethodCall $parentParent */
        $parentParent = $parent->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        $this->removeNode($parentParent);
    }
}
